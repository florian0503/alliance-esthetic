<?php

declare(strict_types=1);

namespace App\Controller;

use App\Entity\Reservation;
use App\Repository\ReservationRepository;
use App\Service\ReservationSlotValidator;
use Doctrine\DBAL\Exception\UniqueConstraintViolationException;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Csrf\CsrfToken;
use Symfony\Component\Security\Csrf\CsrfTokenManagerInterface;

class ReservationController extends AbstractController
{
    private const SESSION_RESERVATION_SUCCESS_ID = 'reservation_last_success_id';

    #[Route('/reservation', name: 'app_reservation')]
    public function index(): Response
    {
        return $this->render('reservation/index.html.twig');
    }

    #[Route('/reservation/slots', name: 'app_reservation_slots', methods: ['GET'])]
    public function slots(ReservationRepository $repo): JsonResponse
    {
        $reservations = $repo->findAll();
        $taken = [];
        foreach ($reservations as $r) {
            $taken[] = [
                'date' => $r->getDateRdv()->format('Y-m-d'),
                'heure' => $r->getHeureRdv(),
            ];
        }

        return new JsonResponse($taken);
    }

    #[Route('/reservation/confirmer', name: 'app_reservation_confirmer', methods: ['GET', 'POST'])]
    public function confirmer(
        Request $request,
        SessionInterface $session,
        EntityManagerInterface $em,
        ReservationRepository $reservationRepository,
        ReservationSlotValidator $slotValidator,
        CsrfTokenManagerInterface $csrfTokenManager,
    ): Response {
        $date    = (string) $request->query->get('date', $request->request->get('date', ''));
        $heure   = (string) $request->query->get('heure', $request->request->get('heure', ''));
        $dateIso = (string) $request->query->get('date_iso', $request->request->get('date_iso', ''));

        if ($date === '' || $heure === '') {
            return $this->redirectToRoute('app_reservation');
        }

        if ($request->isMethod('POST')) {
            $token = (string) $request->request->get('_csrf_token', '');
            if (!$csrfTokenManager->isTokenValid(new CsrfToken('reservation_confirm', $token))) {
                $this->addFlash('danger', 'Session expirée ou formulaire invalide. Merci de réessayer.');

                return $this->redirectToRoute('app_reservation');
            }

            $dateIso = trim((string) $request->request->get('date_iso', ''));
            $heure   = trim((string) $request->request->get('heure', ''));
            $date    = trim((string) $request->request->get('date', ''));

            $slotErrors = $slotValidator->validate($dateIso, $heure);
            if ($slotErrors !== []) {
                foreach ($slotErrors as $msg) {
                    $this->addFlash('danger', $msg);
                }

                return $this->redirectToRoute('app_reservation');
            }

            $canonicalHeure = $slotValidator->normalizeHeureLabel($heure);
            if ($canonicalHeure === null) {
                $this->addFlash('danger', 'Créneau horaire non reconnu.');

                return $this->redirectToRoute('app_reservation');
            }

            $tz = new \DateTimeZone(ReservationSlotValidator::TIMEZONE);
            $dateObj = \DateTimeImmutable::createFromFormat('Y-m-d', $dateIso, $tz);
            if ($dateObj === false) {
                $this->addFlash('danger', 'Date du rendez-vous invalide.');

                return $this->redirectToRoute('app_reservation');
            }
            $dateObj = \DateTime::createFromImmutable($dateObj->setTime(0, 0, 0));

            if ($reservationRepository->existsForSlot($dateObj, $canonicalHeure)) {
                $this->addFlash('danger', 'Ce créneau n’est plus disponible. Choisissez un autre horaire.');

                return $this->redirectToRoute('app_reservation');
            }

            $prenom    = trim((string) $request->request->get('prenom', ''));
            $nom       = trim((string) $request->request->get('nom', ''));
            $email     = trim((string) $request->request->get('email', ''));
            $telephone = trim((string) $request->request->get('tel', ''));

            if ($prenom === '' || $nom === '' || $email === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
                return $this->render('reservation/confirmer.html.twig', [
                    'date'     => $date,
                    'heure'    => $canonicalHeure,
                    'date_iso' => $dateIso,
                    'errors'   => true,
                ]);
            }

            $reservation = new Reservation();
            $reservation->setPrenom($prenom);
            $reservation->setNom($nom);
            $reservation->setEmail($email);
            $reservation->setTelephone($telephone !== '' ? $telephone : null);
            $reservation->setHeureRdv($canonicalHeure);
            $reservation->setDateRdv($dateObj);

            $em->persist($reservation);

            try {
                $em->flush();
            } catch (UniqueConstraintViolationException) {
                $em->clear();
                $this->addFlash('danger', 'Ce créneau vient d’être réservé. Choisissez un autre horaire.');

                return $this->redirectToRoute('app_reservation');
            }

            $session->set(self::SESSION_RESERVATION_SUCCESS_ID, $reservation->getId());

            return $this->redirectToRoute('app_reservation_succes');
        }

        if ($dateIso === '') {
            $this->addFlash('danger', 'Informations de créneau incomplètes.');

            return $this->redirectToRoute('app_reservation');
        }

        $slotErrors = $slotValidator->validate($dateIso, $heure);
        if ($slotErrors !== []) {
            foreach ($slotErrors as $msg) {
                $this->addFlash('danger', $msg);
            }

            return $this->redirectToRoute('app_reservation');
        }

        $canonicalHeure = $slotValidator->normalizeHeureLabel($heure);
        if ($canonicalHeure === null) {
            $this->addFlash('danger', 'Créneau horaire non reconnu.');

            return $this->redirectToRoute('app_reservation');
        }

        return $this->render('reservation/confirmer.html.twig', [
            'date'     => $date,
            'heure'    => $canonicalHeure,
            'date_iso' => $dateIso,
        ]);
    }

    #[Route('/reservation/succes', name: 'app_reservation_succes')]
    public function succes(SessionInterface $session, ReservationRepository $reservationRepository): Response
    {
        $rawId = $session->get(self::SESSION_RESERVATION_SUCCESS_ID);
        $id    = \is_int($rawId) ? $rawId : (int) $rawId;
        if ($id < 1) {
            $this->addFlash('info', 'Pour confirmer un rendez-vous, utilisez le calendrier de réservation.');

            return $this->redirectToRoute('app_reservation');
        }

        $reservation = $reservationRepository->find($id);
        $session->remove(self::SESSION_RESERVATION_SUCCESS_ID);

        if (!$reservation instanceof Reservation) {
            return $this->redirectToRoute('app_reservation');
        }

        $dateLabel = $this->formatFrenchReservationDate($reservation->getDateRdv());

        return $this->render('reservation/succes.html.twig', [
            'prenom' => $reservation->getPrenom(),
            'date'   => $dateLabel,
            'heure'  => $reservation->getHeureRdv(),
        ]);
    }

    private function formatFrenchReservationDate(\DateTimeInterface $date): string
    {
        $fmt = new \IntlDateFormatter(
            'fr_FR',
            \IntlDateFormatter::FULL,
            \IntlDateFormatter::NONE,
            'Europe/Paris',
            \IntlDateFormatter::GREGORIAN,
            'EEEE d MMMM'
        );
        $out = $fmt->format($date);

        return $out !== false ? ucfirst($out) : $date->format('d/m/Y');
    }
}
