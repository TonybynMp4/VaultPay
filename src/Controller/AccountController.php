<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class AccountController extends AbstractController
{
    #[Route('/account', name: 'app_account')]
    public function index(): Response
    {
        // Création d'un utilisateur fictif avec des données d'exemple
        

        // Rendre la vue avec les informations de l'utilisateur
        return $this->render('account/index.html.twig', [
        ]);
    }
}
