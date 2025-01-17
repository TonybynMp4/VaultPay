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
use Symfony\Component\Security\Http\Attribute\IsGranted;

final class AdminController extends AbstractController
{
    #[IsGranted('ROLE_ADMIN')]
    #[Route('/admin', name: 'app_admin')]
    public function index(UsersRepository $usersRepository, BankAccountRepository $bankAccountRepository, TransactionRepository $transactionRepository): Response
    {
        return $this->render('admin/index.html.twig', [
            'users' => $usersRepository->findAll(),
            'accounts' => $bankAccountRepository->findAll(),
            'transactions' => $transactionRepository->findAll(),
        ]);
    }

    #[IsGranted('ROLE_ADMIN')]
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

    #[IsGranted('ROLE_ADMIN')]
    #[Route('/admin/user/{id}/block', name: 'admin_user_block')]
    public function blockUser(int $id, UsersRepository $usersRepository, EntityManagerInterface $entityManager): Response
    {
        $user = $usersRepository->find($id);

        if (!$user) {
            $this->addFlash('error', 'Utilisateur introuvable.');
            return $this->redirectToRoute('app_admin');
        }

        $user->setBlocked(!$user->isBlocked());
        $entityManager->persist($user);
        $entityManager->flush();

        $this->addFlash('success', 'Utilisateur bloqué avec succès.');

        return $this->redirectToRoute('app_admin');
    }

    #[IsGranted('ROLE_ADMIN')]
    #[Route('/admin/account/{id}/block', name: 'admin_account_block')]
    public function blockAccount(int $id, BankAccountRepository $bankAccountRepository, EntityManagerInterface $entityManager): Response
    {
        $account = $bankAccountRepository->find($id);

        if (!$account) {
            $this->addFlash('error', 'Compte bancaire introuvable.');
            return $this->redirectToRoute('app_admin');
        }

        $account->setClose(!$account->isClose());
        $entityManager->persist($account);
        $entityManager->flush();

        if ($account->isClose()) {
            $this->addFlash('success', 'Compte bancaire bloqué avec succès.');
        } else {
            $this->addFlash('success', 'Compte bancaire débloqué avec succès.');
        }

        return $this->redirectToRoute('app_admin');
    }

    #[IsGranted('ROLE_ADMIN')]
    #[Route('/admin/user/{id}/accounts', name: 'admin_user_accounts', methods: ['GET'])]
    public function getUserAccounts(BankAccountRepository $bankAccountRepository, int $id): JsonResponse
    {
        $accounts = $bankAccountRepository->findBy(['Users' => $id]);

        $data = [];
        foreach ($accounts as $account) {
            $data[] = [
                'id' => $account->getId(),
                'name' => $account->getName(),
                'type' => $account->getType(),
                'balance' => $account->getBalance(),
                'closed' => $account->isClose()
            ];
        }

        return new JsonResponse(['accounts' => $data]);
    }

    #[IsGranted('ROLE_ADMIN')]
    #[Route('/admin/account/{id}/transactions', name: 'admin_account_transactions', methods: ['GET'])]
    public function getAccountTransactions(TransactionRepository $transactionRepository, int $id): JsonResponse
    {
        $transactions = $transactionRepository->findBy(['FromAccount' => $id]);

        $data = [];
        foreach ($transactions as $transaction) {
            $data[] = [
                'id' => $transaction->getId(),
                'label' => $transaction->getLabel(),
                'date' => $transaction->getDate()->format('Y-m-d H:i:s'),
                'amount' => $transaction->getAmount(),
                'status' => $transaction->isCancel()
            ];
        }

        return new JsonResponse(['transactions' => $data]);
    }


    #[IsGranted('ROLE_ADMIN')]
    #[Route('/admin/transaction/{id}/cancel', name: 'admin_transaction_cancel')]
    public function cancelTransaction(int $id, TransactionRepository $transactionRepository, EntityManagerInterface $entityManager, BankAccountRepository $bankAccountRepository): Response
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
            $bankAccountRepository->deposit($fromAccount, $amount);
        }

        if ($toAccount) {
            $bankAccountRepository->withdraw($toAccount, $amount);
        }

        $entityManager->persist($transaction);
        $entityManager->flush();

        $this->addFlash('success', 'Transaction annulée avec succès.');

        return $this->redirectToRoute('app_admin');
    }
}
