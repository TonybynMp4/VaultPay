<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

final class EpargneController extends AbstractController
{
   /*  #[IsGranted('ROLE_USER')]
    */ #[Route('/epargne', name: 'app_epargne')]
    public function index(): Response
    {
        return $this->render('epargne/index.html.twig', [
            'controller_name' => 'EpargneController',
        ]);
    }
}
