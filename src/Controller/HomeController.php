<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Attribute\Route;

class HomeController extends AbstractController {
    #[Route('/', name: 'home')]
    public function index() {
        if (!$this->getUser()) {
            return $this->redirectToRoute('app_login');
        }

        $bankAccount = $this->getUser()->getBankAccounts()[0];
        $transactions = array_merge(
            $bankAccount->getOutgoingTransactions()->toArray(),
            $bankAccount->getIncomingTransactions()->toArray()
        );

        $data = [
            'bankAccount' => $bankAccount,
            'transactions' => $transactions,
        ];

        return $this->render('home/index.html.twig', $data);
    }
}
