<?php

namespace App\Controller;

use App\Repository\TransactionRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

class HomeController extends AbstractController {
    #[Route('/', name: 'app_home')]
    public function home(): \Symfony\Component\HttpFoundation\Response {
        return $this->render('home/index.html.twig');
    }

    #[IsGranted('ROLE_USER')]
    #[Route('/dashboard', name: 'app_dashboard')]
    public function dashboard(TransactionRepository $transactionRepository): \Symfony\Component\HttpFoundation\Response {
        $user = $this->getUser();

        if (!$user) {
            return $this->redirectToRoute('app_login');
        }

        // l'erreur n'est qu'une illusion
        $bankAccount = $user->getBankAccounts()->first();

        $transactions = $transactionRepository
            ->findLastTransactionsByAccount($bankAccount->getId(), 5);

        $data = [
            'bankAccount' => $bankAccount,
            'transactions' => $transactions,
        ];

        return $this->render('home/dashboard.html.twig', $data);
    }
}
