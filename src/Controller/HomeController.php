<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class HomeController extends AbstractController {
    #[Route('/', name: 'app_home')]
    public function index(): Response {
        $paiements = [
            ['nom' => 'Virement', 'montant' => 19.00, 'date' => '2025-01-22'],
            ['nom' => 'Virement', 'montant' => -20.00, 'date' => '2025-01-21'],
            ['nom' => 'Depôt', 'montant' => 4870.00, 'date' => '2025-01-12'],
            ['nom' => 'Franprix', 'montant' => 32.99, 'date' => '2025-01-11'],
            ['nom' => 'Virement', 'montant' => -20.00, 'date' => '2025-01-11'],
            ['nom' => 'Virement', 'montant' => -20.00, 'date' => '2025-01-10'],
        ];

        $soldePrincipal = array_reduce($paiements, function ($acc, $paiement) {
            return $acc + $paiement['montant'];
        }, 0);

        return $this->render('home/index.html.twig', [
            'soldePrincipal' => number_format($soldePrincipal, 2, ',', ' '),
            'paiements' => $paiements,
        ]);
    }
}
