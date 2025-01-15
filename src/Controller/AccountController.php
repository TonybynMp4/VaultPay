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
        $user = $this->getUser();

        return $this->render('account/index.html.twig', [
            'user' => $user,
        ]);

        $bankAccounts = $user->getBankAccounts();

        return $this->render('account/index.html.twig', [
            'user' => $user,
            'bankAccounts' => $bankAccounts,
        ]);

        return $this->render('account/index.html.twig', [
        ]);
    }
}
