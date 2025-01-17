<?php

namespace App\Repository;

use App\Entity\BankAccount;
use App\Entity\Transaction;
use App\Entity\Users;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Transaction>
 */
class TransactionRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Transaction::class);
    }

    public function findLastTransactionsByUser(Users $user, int $limit): array
    {
        return $this->createQueryBuilder('t')
            ->leftJoin('t.FromAccount', 'fa')
            ->leftJoin('t.ToAccount', 'ta')
            ->where('fa.Users = :userId OR ta.Users = :userId')
            ->setParameter('userId', $user->getId())
            ->orderBy('t.Date', 'DESC')
            ->setMaxResults($limit)
            ->getQuery()
            ->getResult();
    }

    public function findLastTransactionsByAccount(int $accountId, int $limit): array
    {
        return $this->createQueryBuilder('t')
            ->where('t.FromAccount = :accountId OR t.ToAccount = :accountId')
            ->setParameter('accountId', $accountId)
            ->orderBy('t.Date', 'DESC')
            ->setMaxResults($limit)
            ->getQuery()
            ->getResult();
    }

    // Valide que la transaction est correcte et retourne une string d'erreur si ce n'est pas le cas
    private function validateTransaction(Transaction $transaction, Users $user): string
    {
        $error = "";

        if ($transaction->getFromAccount() === $transaction->getToAccount()) {
            $error = "Les comptes source et destination doivent être différents";
        }

        if (!$transaction->getType() === 0 && (!$transaction->getFromAccount() || !$transaction->getToAccount())) {
            $error = "Un des comptes est introuvable";
        }
        if ($transaction->getType() === 1 && !$transaction->getFromAccount()) {
            $error = "Le compte source est introuvable";
        }

        if ($transaction->getType() === 2 && !$transaction->getToAccount()) {
            $error = "Le compte destination est introuvable";
        }

        if (($transaction->getFromAccount() && $transaction->getFromAccount()->isClose()) || ($transaction->getToAccount() && $transaction->getToAccount()->isClose())) {
            $error = "Un des comptes est fermé";
        }

        if ($transaction->getAmount() <= 0) {
            $error = "Le montant doit être positif";
        }

        if ($transaction->getFromAccount() && $transaction->getFromAccount()->getUser()->getId() !== $user->getId()) {
            $error = "Vous ne pouvez pas effectuer un virement depuis ce compte";
        }

        if ($transaction->getFromAccount() && $transaction->getFromAccount()->getBalance() < $transaction->getAmount()) {
            $error = "Solde insuffisant";
        }

        return $error;
    }

    // Crée une transaction et met à jour les soldes des comptes
    public function createTransaction(Transaction $transaction, Users $user): bool | string
    {
        $validationError = $this->validateTransaction($transaction, $user);
        if ($validationError !== "") {
            return $validationError;
        }

        $label = $transaction->getLabel();

        if (!$label) {
            switch ($transaction->getType()) {
                case 0:
                    $label = 'Virement du compte ' . $transaction->getFromAccount()->getId() . ' au compte ' . $transaction->getToAccount()->getId();
                    break;
                case 1:
                    $label = 'Retrait du compte ' . $transaction->getFromAccount()->getId();
                    break;
                case 2:
                    $label = 'Dépot sur le compte ' . $transaction->getToAccount()->getId();
                    break;
                default:
                    throw new \Exception("Type de transaction inconnu");
                    break;
            }
        }

        $transaction->setLabel($label);
        $transaction->setDate(new \DateTime());
        $transaction->setCancel(false);

        $bankAccountRepository = $this->getEntityManager()->getRepository(BankAccount::class);
        // Met à jour les soldes des comptes
        if ($transaction->getFromAccount() && $transaction->getToAccount()) {
            $bankAccountRepository->transfer($transaction->getFromAccount(), $transaction->getToAccount(), $transaction->getAmount());
        } elseif ($transaction->getFromAccount()) {
            $bankAccountRepository->withdraw($transaction->getFromAccount(), $transaction->getAmount());
        }
        elseif ($transaction->getToAccount()) {
            $bankAccountRepository->deposit($transaction->getToAccount(), $transaction->getAmount());
        } else {
            throw new \Exception("Un des comptes doit être renseigné");
        }

        $this->getEntityManager()->persist($transaction);
        $this->getEntityManager()->flush();

        return true;
    }

    // retourne true si la transaction a été annulée, false sinon
    public function cancelTransaction($transactionId): bool
    {
        $transaction = $this->find($transactionId);
        if ($transaction && !$transaction->isCancel()) {
            $transaction->setCancel(true);
            $this->getEntityManager()->persist($transaction);
            $this->getEntityManager()->flush();
            return true;
        }

        return false;
    }
}
