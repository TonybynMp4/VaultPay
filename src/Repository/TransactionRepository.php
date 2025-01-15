<?php

namespace App\Repository;

use App\Entity\Transaction;
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

    public function findLastTransactionsByAccount(int $accountId, int $limit = 5): array
    {
        return $this->createQueryBuilder('t')
            ->where('t.FromAccount = :accountId OR t.ToAccount = :accountId')
            ->setParameter('accountId', $accountId)
            ->orderBy('t.Date', 'DESC')
            ->setMaxResults($limit)
            ->getQuery()
            ->getResult();
    }
}
