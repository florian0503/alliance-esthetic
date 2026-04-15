<?php

declare(strict_types=1);

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class PolitiqueConfidentialiteController extends AbstractController
{
    #[Route('/politique-de-confidentialite', name: 'app_politique_confidentialite')]
    public function index(): Response
    {
        return $this->render('politique-confidentialite/index.html.twig');
    }
}
