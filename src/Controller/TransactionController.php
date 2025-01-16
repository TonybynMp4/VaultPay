<?php

namespace App\Controller;

use App\Form\TransferFormType;
use App\Repository\BankAccountRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use App\Entity\Transaction;
use App\Entity\Users;
use App\Form\DepositFormType;
use App\Repository\TransactionRepository;

class TransactionController extends AbstractController
{
    #[IsGranted('ROLE_USER')]
    #[Route('/transaction/transfer', name: 'transaction_transfer')]
    public function showVirement(
        Request $request,
        BankAccountRepository $bankAccountRepository,
        EntityManagerInterface $entityManager
    ): Response {
        $form = $this->createForm(TransferFormType::class);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $data = $form->getData();

            $fromAccountId = $data['from_account_id'];
            $toAccountId = $data['to_account_id'];
            $fromAccount = $bankAccountRepository->find($fromAccountId);
            $toAccount = $bankAccountRepository->find($toAccountId);


            $newTransaction = new Transaction();
            $newTransaction
                ->setAmount($data['amount'])
                ->setFromAccount($fromAccount)
                ->setToAccount($toAccount)
                ->setType(0);

            $result = $entityManager->getRepository(Transaction::class)->createTransaction($newTransaction, $this->getUser());
            if ($result === true) {
                $this->addFlash('success', 'Le virement a été effectué avec succès');
                return $this->redirectToRoute('app_dashboard');
            } else {
                $this->addFlash('error', $result);
            }
        }

        return $this->render('transaction/transfer.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    #[IsGranted('ROLE_USER')]
    #[Route('/transaction/deposit', name: 'transaction_deposit')]
    public function showDepot(
        Request $request,
        EntityManagerInterface $entityManager
    ): Response {
        $form = $this->createForm(DepositFormType::class, null, [
            'user' => $this->getUser(),
        ]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $data = $form->getData();

            $newTransaction = new Transaction;
            $newTransaction
                ->setAmount($data['amount'])
                ->setToAccount($data['to_account_id'])
                ->setType(2);

            $result = $entityManager->getRepository(Transaction::class)->createTransaction($newTransaction);
            if ($result === true) {
                $this->addFlash('success', 'Le dépôt a été effectué avec succès');
                return $this->redirectToRoute('app_account');
            } else {
                $this->addFlash('error', $result);
            }
        }

        return $this->render('transaction/deposit.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    #[IsGranted('ROLE_USER')]
    #[Route('/transaction/withdraw', name: 'transaction_withdraw')]
    public function showRetrait(
        Request $request,
        TransactionRepository $transactionRepository,
    ): Response {
        $form = $this->createForm(TransferFormType::class);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $data = $form->getData();

            $newTransaction = new Transaction;
            $newTransaction
                ->setAmount($data['amount'])
                ->setFromAccount($data['from_account_id'])
                ->setType(1);

            $result = $transactionRepository->createTransaction($newTransaction);
            if ($result === true) {
                $this->addFlash('success', 'Le retrait a été effectué avec succès');
                return $this->redirectToRoute('app_account');
            } else {
                $this->addFlash('error', $result);
            }
        }

        return $this->render('transaction/withdraw.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    #[IsGranted('ROLE_USER')]
    #[Route('/transaction/history', name: 'transaction_history')]
    public function showHistory(
        TransactionRepository $transactionRepository
    ): Response {
        $user = $this->getUser();
        $accounts = $user->getBankAccounts();
        $transactions = [];

        foreach ($accounts as $account) {
            $accountTransactions = $transactionRepository->findLastTransactionsByAccount($account->getId(), 100);
            $transactions = array_merge($transactions, $accountTransactions);
        }

        usort($transactions, function ($a, $b) {
            return $b->getDate() <=> $a->getDate();
        });

        return $this->render('transaction/history.html.twig', [
            'transactions' => $transactions,
        ]);

    }
}
