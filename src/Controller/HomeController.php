<?php

namespace App\Controller;

use App\Entity\Transaction;
use App\Repository\TransactionRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Attribute\Route;

class HomeController extends AbstractController {
    #[Route('/', name: 'app_home')]
    public function home(): \Symfony\Component\HttpFoundation\Response {
        return $this->render('home/index.html.twig');
    }

    #[Route('/dashboard', name: 'app_dashboard')]
    public function dashboard(TransactionRepository $transactionRepository): \Symfony\Component\HttpFoundation\Response {
        $user = $this->getUser();

        if (!$user) {
            return $this->redirectToRoute('app_login');
        }

        // l'erreur n'est qu'une illusion
        $bankAccount = $user->getBankAccounts()->first();
        // use repository to get transactions
        $transactions = $transactionRepository
            ->findLastTransactionsByAccount($bankAccount->getId(), 5);

        $data = [
            'bankAccount' => $bankAccount,
            'transactions' => $transactions,
        ];

        return $this->render('home/dashboard.html.twig', $data);
    }
}
