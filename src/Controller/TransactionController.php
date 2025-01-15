<?php

namespace App\Controller;

use App\Entity\Transaction;
use App\Form\TransactionType;
use App\Repository\BankAccountRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

class TransactionController extends AbstractController
{
    #[IsGranted('ROLE_USER')]
    #[Route('/transaction-test', name: 'app_transaction')]
    public function testTransaction(
        Request $request,
        BankAccountRepository $bankAccountRepository,
        EntityManagerInterface $entityManager
    ): Response {
        $form = $this->createForm(TransactionType::class);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $data = $form->getData();

            $fromAccountId = $data['from_account_id'];
            $toAccountId = $data['to_account_id'];
            $amount = $data['amount'];
            $label = $data['label'] ?? null;
            $type = $data['type'];

            // Validation des données
            if ($amount <= 0) {
                $this->addFlash('error', 'Le montant doit être positif.');
            } else {
                $fromAccount = $bankAccountRepository->find($fromAccountId);
                $toAccount = $bankAccountRepository->find($toAccountId);

                if (!$fromAccount || !$toAccount) {
                    $this->addFlash('error', 'Un des comptes est introuvable.');
                } /* elseif ($fromAccount->getUserId() !== $this->getUser()) {
                    $this->addFlash('error', 'Vous ne pouvez pas effectuer un virement depuis ce compte.');
                } */ elseif ($fromAccount->getSolde() < $amount) {
                    $this->addFlash('error', 'Solde insuffisant.');
                } elseif ($fromAccount->isClose()) {
                    $this->addFlash('error', 'Le compte source est fermé.');
                } elseif ($toAccount->isClose()) {
                    $this->addFlash('error', 'Le compte destinataire est fermé.');
                } else {
                    // Effectuer le virement
                    $fromAccount->setSolde($fromAccount->getSolde() - $amount);
                    $toAccount->setSolde($toAccount->getSolde() + $amount);

                    if (!$label) {
                        $label = $type === 'virement' ? 'Virement du compte ' . $fromAccount->getId() . ' au compte ' . $toAccount->getId() : 'Dépôt sur le compte ' . $toAccount->getId();
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
                }
            }
            return $this->redirectToRoute('app_transaction');
        }

        return $this->render('transaction/test.html.twig', [
            'form' => $form->createView(),
        ]);
    }
}
