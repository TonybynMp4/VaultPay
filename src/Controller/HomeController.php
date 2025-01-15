<?php

namespace App\Controller;

use App\Entity\Transaction;
use App\Repository\TransactionRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Attribute\Route;

class HomeController extends AbstractController {
    #[Route('/', name: 'home')]
    public function index(TransactionRepository $transactionRepository): \Symfony\Component\HttpFoundation\Response {
       /*  if (!$this->getUser()) {
            return $this->redirectToRoute('app_login');
        } */

        $bankAccount = $this->getUser()->getBankAccounts()[0];
        // use repository to get transactions
        $transactions = $transactionRepository
            ->findLastTransactionsByAccount($bankAccount->getId(), 5);

        $data = [
            'bankAccount' => $bankAccount,
            'transactions' => $transactions,
        ];

        return $this->render('home/index.html.twig', $data);
    }
}
