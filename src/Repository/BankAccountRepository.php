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
        $accounts = $user->getBankAccounts();

        foreach ($accounts as $account) {
            $totalBalance += $account->getBalance();
        }

        return $totalBalance;
    }
}