<?php

namespace App\Repository;

use App\Entity\BankAccount;
use App\Entity\Users;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<BankAccount>
 */
class BankAccountRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, BankAccount::class);
    }

    public function getTotalBalance(Users $user): float
    {
        $totalBalance = 0;
        $accounts = $user->getOpenBankAccounts();

        foreach ($accounts as $account) {
            $totalBalance += $account->getBalance();
        }

        return $totalBalance;
    }

    private function validateTransaction(?BankAccount $fromAccount, ?BankAccount $toAccount, float $amount): string
    {
        if ($amount < 0) {
            return 'Le montant doit être positif.';
        }

        if ($fromAccount && $fromAccount->getBalance() < $amount) {
            return 'Le compte source ne contient pas assez d\'argent.';
        }

        if ($fromAccount && $toAccount && $fromAccount === $toAccount) {
            return 'Vous ne pouvez pas transférer de l\'argent sur le même compte.';
        }

        if ($fromAccount && $fromAccount->getType() === 2 && $fromAccount->getBalance() - $amount < 0) {
            return 'Vous ne pouvez pas retirer plus d\'argent que ce que vous avez sur un compte épargne.';
        }

        if (($toAccount && $toAccount->getType() !== 2) && ($fromAccount && $fromAccount->getBalance() - $amount < -400)) {
            return 'Le découvert maximum autorisé est de 400€.';
        }

        return "";
    }

    public function withdraw(BankAccount $account, float $amount): string
    {
        $validationError = $this->validateTransaction($account, null, $amount);
        if ($validationError !== "") {
            return $validationError;
        }

        $em = $this->getEntityManager();
        $account->setBalance($account->getBalance() - $amount);
        $em->persist($account);
        $em->flush();
        return 'ok';
    }

    public function deposit(BankAccount $account, float $amount): string
    {
        $error = $this->validateTransaction(null, $account, $amount);
        if ($error) {
            return $error;
        }

        $em = $this->getEntityManager();
        $account->setBalance($account->getBalance() + $amount);
        $em->persist($account);
        $em->flush();
        return 'ok';
    }

    public function transfer(BankAccount $fromAccount, BankAccount $toAccount, float $amount): string
    {
        $error = $this->validateTransaction($fromAccount, $toAccount, $amount);
        if ($error) {
            return $error;
        }

        $em = $this->getEntityManager();

        $fromAccount->setBalance($fromAccount->getBalance() - $amount);
        $toAccount->setBalance($toAccount->getBalance() + $amount);

        $em->persist($fromAccount);
        $em->persist($toAccount);
        $em->flush();
        return 'ok';
    }
}