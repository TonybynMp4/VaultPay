<?php

namespace App\Controller;

use App\Entity\Users;
use App\Entity\BankAccount;
use App\Entity\Transaction;
use App\Repository\UsersRepository;
use App\Repository\BankAccountRepository;
use App\Repository\TransactionRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class AdminController extends AbstractController
{
    private $entityManager;
    private $userRepository;
    private $bankAccountRepository;
    private $transactionRepository;

    public function __construct(EntityManagerInterface $entityManager, UsersRepository $userRepository, BankAccountRepository $bankAccountRepository, TransactionRepository $transactionRepository)
    {
        $this->entityManager = $entityManager;
        $this->userRepository = $userRepository;
        $this->bankAccountRepository = $bankAccountRepository;
        $this->transactionRepository = $transactionRepository;
    }

    /**
     * @Route("/admin/users", name="admin_list_users")
     */
    public function listUsers(): Response
    {
        $users = $this->userRepository->findAll();
        return $this->render('admin/users.html.twig', ['users' => $users]);
    }

    /**
     * @Route("/admin/user/toggle/{id}", name="admin_toggle_user_status")
     */
    public function toggleUserStatus(int $id): RedirectResponse
    {
        $user = $this->userRepository->find($id);
        if ($user) {
            foreach ($user->getBankAccounts() as $account) {
                $account->setClose(!$account->isClose());
            }
            $this->entityManager->flush();
        }
        return $this->redirectToRoute('admin_list_users');
    }

    /**
     * @Route("/admin/accounts", name="admin_list_accounts")
     */
    public function listAccounts(): Response
    {
        $accounts = $this->bankAccountRepository->findAll();
        return $this->render('admin/accounts.html.twig', ['accounts' => $accounts]);
    }

    /**
     * @Route("/admin/account/toggle/{id}", name="admin_toggle_account_status")
     */
    public function toggleAccountStatus(int $id): RedirectResponse
    {
        $account = $this->bankAccountRepository->find($id);
        if ($account) {
            $account->setClose(!$account->isClose());
            $this->entityManager->flush();
        }
        return $this->redirectToRoute('admin_list_accounts');
    }

    /**
     * @Route("/admin/transactions", name="admin_list_transactions")
     */
    public function listTransactions(): Response
    {
        $transactions = $this->transactionRepository->findAll();
        return $this->render('admin/transactions.html.twig', ['transactions' => $transactions]);
    }

    /**
     * @Route("/admin/transaction/cancel/{id}", name="admin_cancel_transaction")
     */
    public function cancelTransaction(int $id): RedirectResponse
    {
        $transaction = $this->transactionRepository->find($id);
        if ($transaction && !$transaction->isCancel()) {
            $transaction->setCancel(true);
            $this->entityManager->flush();
        }
        return $this->redirectToRoute('admin_list_transactions');
    }
}
