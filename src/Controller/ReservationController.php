<?php

declare(strict_types=1);

namespace App\Controller;

use App\Entity\Reservation;
use App\Repository\ReservationRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class ReservationController extends AbstractController
{
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
    public function confirmer(Request $request, EntityManagerInterface $em): Response
    {
        $date    = $request->query->get('date', $request->request->get('date', ''));
        $heure   = $request->query->get('heure', $request->request->get('heure', ''));
        $dateIso = $request->query->get('date_iso', $request->request->get('date_iso', ''));

        if (!$date || !$heure) {
            return $this->redirectToRoute('app_reservation');
        }

        if ($request->isMethod('POST')) {
            $prenom    = trim($request->request->get('prenom', ''));
            $nom       = trim($request->request->get('nom', ''));
            $email     = trim($request->request->get('email', ''));
            $telephone = trim($request->request->get('tel', ''));

            if (!$prenom || !$nom || !$email) {
                return $this->render('reservation/confirmer.html.twig', [
                    'date'     => $date,
                    'heure'    => $heure,
                    'date_iso' => $dateIso,
                    'errors'   => true,
                ]);
            }

            $reservation = new Reservation();
            $reservation->setPrenom($prenom);
            $reservation->setNom($nom);
            $reservation->setEmail($email);
            $reservation->setTelephone($telephone ?: null);
            $reservation->setHeureRdv($heure);

            // date passée en paramètre ex: "Lundi 21 avril" → on stocke la date ISO depuis le hidden field
            $dateParam = trim($request->request->get('date_iso', ''));
            try {
                $dateObj = $dateParam ? new \DateTime($dateParam) : new \DateTime();
            } catch (\Exception) {
                $dateObj = new \DateTime();
            }
            $reservation->setDateRdv($dateObj);

            $em->persist($reservation);
            $em->flush();

            return $this->redirectToRoute('app_reservation_succes', [
                'prenom' => $prenom,
                'date'   => $date,
                'heure'  => $heure,
            ]);
        }

        return $this->render('reservation/confirmer.html.twig', [
            'date'     => $date,
            'heure'    => $heure,
            'date_iso' => $dateIso,
        ]);
    }

    #[Route('/reservation/succes', name: 'app_reservation_succes')]
    public function succes(Request $request): Response
    {
        $prenom = $request->query->get('prenom', '');
        $date   = $request->query->get('date', '');
        $heure  = $request->query->get('heure', '');

        return $this->render('reservation/succes.html.twig', [
            'prenom' => $prenom,
            'date'   => $date,
            'heure'  => $heure,
        ]);
    }
}
