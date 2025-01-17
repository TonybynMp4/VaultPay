<?php

namespace App\Controller;

use App\Entity\BankAccount;
use App\Entity\Transaction;
use App\Form\BankAccountType;
use App\Repository\BankAccountRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

final class AccountController extends AbstractController
{
    #[IsGranted('ROLE_USER')]
    #[Route('/account', name: 'app_account')]
    public function index(): Response
    {
        $user = $this->getUser();

        $bankAccounts = $user->getOpenBankAccounts();

        return $this->render('account/index.html.twig', [
            'user' => $user,
            'bankAccounts' => $bankAccounts,
        ]);
    }

    #[IsGranted('ROLE_USER')]
    #[Route('/account/create', name: 'app_account_create')]
    public function createBankAccount(Request $request, EntityManagerInterface $entityManager, BankAccountRepository $bankAccountRepository): Response
    {
        // Vérifie que l'utilisateur est connecté
        $user = $this->getUser();
        if (!$user) {
            throw $this->createAccessDeniedException('Vous devez être connecté pour créer un compte bancaire.');
        }

        $bankAccount = new BankAccount();
        $form = $this->createForm(BankAccountType::class, $bankAccount);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            if ($bankAccount->getType() === 0) {
                $this->addFlash('error', 'Vous ne pouvez pas créer de compte principal.');
                return $this->redirectToRoute('app_account_create');
            }

            if ($user->getOpenBankAccounts()->count() >= 5) {
                $this->addFlash('error', 'Vous ne pouvez pas créer plus de 5 comptes bancaires.');
                return $this->redirectToRoute('app_account_create');
            }

            if ($bankAccount->getType() === 2 && $bankAccount->getBalance() < 10) {
                $this->addFlash('error', 'Le montant initial pour une compte épargne doit être d\'au moins 10€.');
                return $this->redirectToRoute('app_account_create');
            }

            $mainAccount = $user->getMainBankAccount();
            if (!$bankAccountRepository->withdraw($mainAccount, $bankAccount->getBalance()) === 'ok') {
                $this->addFlash('error', 'Vous n\'avez pas assez d\'argent sur votre compte principal.');
                return $this->redirectToRoute('app_account_create');
            }

            // Associe l'utilisateur connecté au compte bancaire
            $bankAccount->setUser($user);

            // créer la transaction pour le montant initial
            $transaction = new Transaction();
            $transaction->setFromAccount($user->getMainBankAccount());
            $transaction->setToAccount($bankAccount);
            $transaction->setAmount($bankAccount->getBalance());
            $transaction->setDate(new \DateTime());


            // soustrait le montant initial du compte principal

            $entityManager->persist($bankAccount);
            $entityManager->persist($transaction);
            $entityManager->persist($mainAccount);

            $entityManager->flush();

            // Redirection après succès
            return $this->redirectToRoute('app_account'); // Route pour la liste des comptes
        }

        return $this->render('account/create.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    #[IsGranted('ROLE_USER')]
    #[Route('/account/delete/{id}', name: 'app_account_delete')]
    public function deleteBankAccount(int $id, EntityManagerInterface $entityManager): Response
    {
        // Récupère le compte à supprimer
        $bankAccount = $entityManager->getRepository(BankAccount::class)->find($id);

        // Vérifie que le compte existe et appartient à l'utilisateur connecté
        if (!$bankAccount || $bankAccount->getUser() !== $this->getUser()) {
            throw $this->createAccessDeniedException('Vous ne pouvez pas supprimer ce compte.');
        }

        if ($bankAccount->getType() === 0) {
            $this->addFlash('error', 'Vous ne pouvez pas supprimer votre compte principal.');
            return $this->redirectToRoute('app_account');
        }

        // cloture le compte
        $bankAccount->setClose(true);

        $entityManager->persist($bankAccount);
        $entityManager->flush();

        // Redirection après suppression
        return $this->redirectToRoute('app_account'); // Route pour la liste des comptes
    }
}
