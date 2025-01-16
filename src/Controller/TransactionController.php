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
            $amount = $data['amount'];
            $label = $data['label'] ?? null;
            $type = 0;

            $fromAccount = $bankAccountRepository->find($fromAccountId);
            $toAccount = $bankAccountRepository->find($toAccountId);

            $error = false;

            if ($fromAccountId === $toAccountId) {
                $this->addFlash('error', 'Les comptes source et destination doivent être différents.');
                $error = true;
            }

            if ($amount <= 0) {
                $this->addFlash('error', 'Le montant doit être positif.');
                $error = true;
            }

            if (!$fromAccount || !$toAccount) {
                $this->addFlash('error', 'Un des comptes est introuvable.');
                $error = true;
            }

            if ($fromAccount->getUserId() !== $this->getUser()) {
                $this->addFlash('error', 'Vous ne pouvez pas effectuer un virement depuis ce compte.');
                $error = true;
            }

            if ($fromAccount->getBalance() < $amount) {
                $this->addFlash('error', 'Solde insuffisant.');
                $error = true;
            } elseif ($fromAccount->isClose()) {
                $this->addFlash('error', 'Le compte source est fermé.');
                $error = true;
            } elseif ($toAccount->isClose()) {
                $this->addFlash('error', 'Le compte destinataire est fermé.');
                $error = true;
            }
            if ($error)
                return $this->redirectToRoute('transaction_transfer');

            $fromAccount->setBalance($fromAccount->getBalance() - $amount);
            $toAccount->setBalance($toAccount->getBalance() + $amount);

            if (!$label) {
                $label = 'Virement du compte ' . $fromAccount->getId() . ' au compte ' . $toAccount->getId();
            }

            // Enregistrer la transaction
            $transaction = new Transaction();
            $transaction->setFromAccount($fromAccount);
            $transaction->setToAccount($toAccount);
            $transaction->setAmount($amount);
            $transaction->setType($type);
            $transaction->setLabel($label);
            $transaction->setDate(new \DateTime());
            $transaction->setCancel(false);

            $entityManager->persist($transaction);
            $entityManager->flush();

            $this->addFlash('success', 'Transaction effectuée avec succès.');
            return $this->redirectToRoute('app_dashboard');
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
            $amount = $data->getAmount();
            $toAccount = $data->getToAccount();
            $label = $data->getLabel() ?? "Dépôt sur le compte " . $toAccount->getId();

            // Validation des données
            if ($amount <= 0) {
                $this->addFlash('error', 'Le montant doit être positif.');
            } else {
                // Create new transaction
                $transaction = new Transaction();
                $transaction->setAmount($amount);
                $transaction->setLabel($label);
                $transaction->setType(2); // Deposit type
                $transaction->setToAccount($toAccount);
                $transaction->setDate(new \DateTime());

                // Update account balance
                $newBalance = $toAccount->getBalance() + $amount;
                $toAccount->setBalance($newBalance);

                // Save changes
                $entityManager->persist($transaction);
                $entityManager->flush();

                $this->addFlash('success', 'Le dépôt a été effectué avec succès.');
                return $this->redirectToRoute('app_account');
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
        BankAccountRepository $bankAccountRepository,
        EntityManagerInterface $entityManager
    ): Response {
        $form = $this->createForm(TransferFormType::class);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $data = $form->getData();

            $fromAccountId = $data['from_account_id'];
            $amount = $data['amount'];
            $label = $data['label'] ?? null;
            $type = 2;

            // Validation des données
            if ($amount <= 0) {
                $this->addFlash('error', 'Le montant doit être positif.');
            } else {
                $fromAccount = $bankAccountRepository->find($fromAccountId);

                if (!$fromAccount) {
                    $this->addFlash('error', 'Le compte est introuvable.');
                } elseif ($fromAccount->getBalance() < $amount) {
                    $this->addFlash('error', 'Solde insuffisant.');
                } elseif ($fromAccount->isClose()) {
                    $this->addFlash('error', 'Le compte est fermé.');
                } else {
                    // Effectuer le retrait
                    $fromAccount->setBalance($fromAccount->getBalance() - $amount);

                    if (!$label) {
                        $label = 'Retrait sur le compte ' . $fromAccount->getId();
                    }

                    // Enregistrer la transaction
                    $transaction = new Transaction();
                    $transaction->setFromAccount($fromAccount);
                    $transaction->setAmount($amount);
                    $transaction->setType($type);
                    $transaction->setLabel($label);
                    $transaction->setDate(new \DateTime());
                    $transaction->setCancel(false);

                    $entityManager->persist($transaction);
                    $entityManager->flush();

                    $this->addFlash('success', 'Transaction effectuée avec succès.');
                }
            }
            return $this->redirectToRoute('transaction_withdraw');
        }

        return $this->render('transaction/withdraw.html.twig', [
            'form' => $form->createView(),
        ]);
    }
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
