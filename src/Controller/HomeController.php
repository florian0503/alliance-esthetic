<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class HomeController extends AbstractController
{
    #[Route('/', name: 'app_home')]
    public function index(): Response
    {
        $testimonials = [
            [
                'name'      => 'Thomas M.',
                'rating'    => 5,
                'text'      => 'Résultat incroyable après ma greffe FUE. L\'équipe est professionnelle et rassurante du début à la fin. Je ne regrette absolument pas mon choix.',
                'procedure' => 'Greffe FUE',
                'date'      => 'Mars 2026',
            ],
            [
                'name'      => 'Karim B.',
                'rating'    => 5,
                'text'      => 'Technique DHI impeccable. Dès 6 mois, les résultats sont déjà très visibles. L\'équipe m\'a accompagné tout au long du processus, je recommande vivement.',
                'procedure' => 'Technique DHI',
                'date'      => 'Février 2026',
            ],
            [
                'name'      => 'Alexandre D.',
                'rating'    => 5,
                'text'      => 'Ma barbe est enfin dense et naturelle. Intervention sans douleur, récupération rapide en 7 jours. Merci à toute l\'équipe d\'Alliance Esthetic.',
                'procedure' => 'Greffe de barbe',
                'date'      => 'Janvier 2026',
            ],
        ];

        $services = [
            [
                'title'       => 'Greffe FUE',
                'description' => 'Extraction folliculaire unitaire, technique mini-invasive avec reprise d\'activité en 7 jours. Résultats naturels et durables.',
                'featured'    => false,
            ],
            [
                'title'       => 'Technique DHI',
                'description' => 'Implantation directe avec stylo Choi pour une densité maximale et une direction parfaitement naturelle.',
                'featured'    => true,
            ],
            [
                'title'       => 'Greffe de Barbe',
                'description' => 'Densification ou tracé de barbe selon votre morphologie. Résultats durables visibles dès les 7 premiers jours.',
                'featured'    => false,
            ],
        ];

        return $this->render('home/index.html.twig', [
            'testimonials' => $testimonials,
            'services'     => $services,
        ]);
    }
}
