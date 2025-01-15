<?php

namespace App\Controller;

use App\Entity\BankAccount;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Doctrine\ORM\EntityManagerInterface;  // ✅ Correct

final class EpargneController extends AbstractController
{
    /*  #[IsGranted('ROLE_USER')]
     */
    #[Route('/epargne', name: 'app_epargne')]
    public function index(): Response
    {
        return $this->render('epargne/index.html.twig', [
            'controller_name' => 'EpargneController',
        ]);
    }
    #[Route('/epargne/souscrire', name: 'app_epargne_souscrire')]
    #[IsGranted('ROLE_USER')]
    public function souscrire(EntityManagerInterface $entityManager): Response
    {
        $user = $this->getUser();

        if (!$user) {
            $this->addFlash('error', 'Vous devez être connecté pour souscrire.');
            return $this->redirectToRoute('app_login');  // Redirection vers la page de connexion
        }

        // Vérifier s'il a moins de 5 comptes
        if (count($user->getBankAccounts()) >= 5) {
            $this->addFlash('error', 'Vous avez atteint le nombre maximal de comptes.');
            return $this->redirectToRoute('app_epargne');
        }

        // Créer un compte épargne
        $compte = new BankAccount();
        $compte->setUserId($user);
        $compte->setSolde(10.00);
        $compte->setClose(false);

        $entityManager->persist($compte);
        $entityManager->flush();

        $this->addFlash('success', 'Votre compte épargne a été créé avec succès !');
        return $this->redirectToRoute('app_epargne');
    }
}
