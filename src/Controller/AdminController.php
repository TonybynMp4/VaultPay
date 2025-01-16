<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use App\Repository\UsersRepository;
use App\Repository\BankAccountRepository;
use App\Repository\TransactionRepository;
use Symfony\Component\HttpFoundation\JsonResponse;
use Doctrine\ORM\EntityManagerInterface;

final class AdminController extends AbstractController
{
    #[Route('/admin', name: 'app_admin')]
    public function index(UsersRepository $usersRepository): Response
    {
        return $this->render('admin/index.html.twig', [
            'users' => $usersRepository->findAll(),
        ]);
    }

    #[Route('/admin/user/{id}/transactions', name: 'admin_user_transactions')]
    public function userTransactions(
        int $id,
        BankAccountRepository $bankAccountRepository,
        TransactionRepository $transactionRepository
    ): Response {
        // Récupérer les comptes bancaires de l'utilisateur
        $bankAccounts = $bankAccountRepository->createQueryBuilder('b')
            ->where('b.Users = :userId')
            ->setParameter('userId', $id)
            ->getQuery()
            ->getResult();

        if (empty($bankAccounts)) {
            return new Response('<p>Aucun compte bancaire trouvé pour cet utilisateur.</p>', 404);
        }

        // Récupérer les IDs des comptes bancaires
        $bankAccountIds = array_map(fn($account) => $account->getId(), $bankAccounts);

        // Récupérer les transactions liées à ces comptes
        $transactions = $transactionRepository->createQueryBuilder('t')
            ->where('t.FromAccount IN (:bankAccountIds) OR t.ToAccount IN (:bankAccountIds)')
            ->setParameter('bankAccountIds', $bankAccountIds)
            ->getQuery()
            ->getResult();

        if (empty($transactions)) {
            return new Response('<p>Aucune transaction trouvée pour cet utilisateur.</p>', 404);
        }

        return $this->render('admin/user_transactions.html.twig', [
            'transactions' => $transactions,
        ]);
    }

    #[Route('/admin/user/{id}/block', name: 'admin_user_block')]
    public function blockUser(int $id, UsersRepository $usersRepository, EntityManagerInterface $entityManager): Response
    {
        $user = $usersRepository->find($id);

        if (!$user) {
            $this->addFlash('error', 'Utilisateur introuvable.');
            return $this->redirectToRoute('app_admin');
        }

        $user->setBlocked(1);
        $entityManager->persist($user);
        $entityManager->flush();

        $this->addFlash('success', 'Utilisateur bloqué avec succès.');

        return $this->redirectToRoute('app_admin');
    }

    #[Route('/admin/transaction/{id}/cancel', name: 'admin_transaction_cancel')]
    public function cancelTransaction(int $id, TransactionRepository $transactionRepository, EntityManagerInterface $entityManager): Response
    {
        $transaction = $transactionRepository->find($id);

        if (!$transaction) {
            $this->addFlash('error', 'Transaction introuvable.');
            return $this->redirectToRoute('app_admin');
        }

        $fromAccount = $transaction->getFromAccount();
        $toAccount = $transaction->getToAccount();
        $amount = $transaction->getAmount();

        if ($transaction->isCancel()) {
            $this->addFlash('error', 'La transaction est déjà annulée.');
            return $this->redirectToRoute('app_admin');
        }

        // Annuler la transaction
        $transaction->setCancel(1);

        // Mettre à jour les soldes des comptes
        if ($fromAccount) {
            $fromAccount->setBalance($fromAccount->getBalance() + $amount);
        }

        if ($toAccount) {
            $toAccount->setBalance($toAccount->getBalance() - $amount);
        }

        $entityManager->persist($transaction);
        $entityManager->flush();

        $this->addFlash('success', 'Transaction annulée avec succès.');

        return $this->redirectToRoute('app_admin');
    }
}