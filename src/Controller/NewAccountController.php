<?php

namespace App\Controller;

use App\Entity\BankAccount;
use App\Form\BankAccountType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class NewAccountController extends AbstractController
{
    #[Route('/newaccount', name: 'new_account')]
    public function newAccount(Request $request, EntityManagerInterface $entityManager): Response
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
            // Associe l'utilisateur connecté au compte bancaire
            $bankAccount->setUserId($user);

            // Définit les valeurs par défaut
            $bankAccount->setSolde($bankAccount->getSolde() ?? 0.0); // Définit à 0 si non fourni
            $bankAccount->setClose(false);

            // Enregistre le compte dans la base de données
            $entityManager->persist($bankAccount);
            $entityManager->flush();

            // Redirection après succès
            return $this->redirectToRoute('app_account'); // Route pour la liste des comptes
        }

        return $this->render('account/new.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    #[Route('/deleteaccount/{id}', name: 'delete_account', methods: ['POST'])]
    public function deleteAccount(int $id, Request $request, EntityManagerInterface $entityManager): Response
    {
        // Récupère le compte à supprimer
        $bankAccount = $entityManager->getRepository(BankAccount::class)->find($id);

        // Vérifie que le compte existe et appartient à l'utilisateur connecté
        if (!$bankAccount || $bankAccount->getUserId() !== $this->getUser()) {
            throw $this->createAccessDeniedException('Vous ne pouvez pas supprimer ce compte.');
        }

        // Vérifie le token CSRF
        if (!$this->isCsrfTokenValid('delete' . $bankAccount->getId(), $request->request->get('_token'))) {
            throw $this->createAccessDeniedException('Token CSRF invalide.');
        }

        // Supprime le compte
        $entityManager->remove($bankAccount);
        $entityManager->flush();

        // Redirection après suppression
        return $this->redirectToRoute('app_account'); // Route pour la liste des comptes
    }
}
