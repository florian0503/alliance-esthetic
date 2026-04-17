<?php

declare(strict_types=1);

namespace App\Controller;

use App\Entity\ContactMessage;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class ContactController extends AbstractController
{
    #[Route('/contact', name: 'app_contact', methods: ['GET', 'POST'])]
    public function index(Request $request, EntityManagerInterface $em): Response
    {
        if ($request->isMethod('POST')) {
            $nom     = trim((string) $request->request->get('name', ''));
            $email   = trim((string) $request->request->get('email', ''));
            $motif   = trim((string) $request->request->get('motif', ''));
            $message = trim((string) $request->request->get('message', ''));
            $rgpd    = $request->request->get('rgpd');

            $errors = [];
            if ($nom === '')                                 $errors[] = 'Le nom est requis.';
            if ($email === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) $errors[] = 'Email invalide.';
            if ($motif === '')                               $errors[] = 'Veuillez choisir un soin.';
            if (!$rgpd)                                      $errors[] = 'Vous devez accepter la politique de confidentialité.';

            if (!$errors) {
                $contact = new ContactMessage();
                $contact->setNom($nom);
                $contact->setEmail($email);
                $contact->setMotif($motif);
                $contact->setMessage($message ?: null);

                $em->persist($contact);
                $em->flush();

                $this->addFlash('contact_success', 'Votre message a bien été envoyé. Nous revenons vers vous sous 24h.');
                return $this->redirectToRoute('app_contact');
            }

            foreach ($errors as $e) {
                $this->addFlash('contact_error', $e);
            }

            return $this->render('contact/index.html.twig', [
                'old' => compact('nom', 'email', 'motif', 'message'),
            ]);
        }

        return $this->render('contact/index.html.twig');
    }
}
