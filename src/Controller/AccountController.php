<?php

namespace App\Controller;

use App\Entity\BankAccount;
use App\Form\BankAccountType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class AccountController extends AbstractController
{
    #[Route('/account', name: 'app_account')]
    public function index(): Response
    {
        $user = $this->getUser();

        return $this->render('account/index.html.twig', [
            'user' => $user,
        ]);

        $bankAccounts = $user->getBankAccounts();

        return $this->render('account/index.html.twig', [
            'user' => $user,
            'bankAccounts' => $bankAccounts,
        ]);

        return $this->render('account/index.html.twig', [
        ]);
    }

    #[Route('/account/create', name: 'app_account_create')]
    public function createBankAccount(Request $request, EntityManagerInterface $entityManager): Response
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
            if ($user->getBankAccounts()->count() >= 5) {
                $this->addFlash('error', 'Vous ne pouvez pas créer plus de 5 comptes bancaires.');
                return $this->redirectToRoute('app_account_create');
            }

            if ($bankAccount->getType() === 1 && $bankAccount->getBalance() < 10) {
                $this->addFlash('error', 'Le montant initial pour une compte épargne doit être d\'au moins 10€.');
                return $this->redirectToRoute('app_account_create');
            }

            // Associe l'utilisateur connecté au compte bancaire
            $bankAccount->setUserId($user);

            // Enregistre le compte dans la base de données
            $entityManager->persist($bankAccount);
            $entityManager->flush();

            // Redirection après succès
            return $this->redirectToRoute('app_account'); // Route pour la liste des comptes
        }

        return $this->render('account/create.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    #[Route('/account/delete/{id}', name: 'app_account_delete', methods: ['POST'])]
    public function deleteBankAccount(int $id, Request $request, EntityManagerInterface $entityManager): Response
    {
        // Récupère le compte à supprimer
        $bankAccount = $entityManager->getRepository(BankAccount::class)->find($id);

        // Vérifie que le compte existe et appartient à l'utilisateur connecté
        if (!$bankAccount || $bankAccount->getUserId() !== $this->getUser()) {
            throw $this->createAccessDeniedException('Vous ne pouvez pas supprimer ce compte.');
        }

        // Supprime le compte
        $entityManager->remove($bankAccount);
        $entityManager->flush();

        // Redirection après suppression
        return $this->redirectToRoute('app_account'); // Route pour la liste des comptes
    }
}
