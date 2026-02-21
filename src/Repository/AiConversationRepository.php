<?php

namespace App\Repository;

use App\Entity\AiConversation;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<AiConversation>
 */
class AiConversationRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, AiConversation::class);
    }

    public function findByUserId(int $userId, int $limit = 50): array
    {
        return $this->createQueryBuilder('c')
            ->andWhere('c.userId = :userId')
            ->setParameter('userId', $userId)
            ->orderBy('c.createdAt', 'DESC')
            ->setMaxResults($limit)
            ->getQuery()
            ->getResult();
    }

    public function findRecentByUserId(int $userId, int $limit = 10): array
    {
        return $this->createQueryBuilder('c')
            ->andWhere('c.userId = :userId')
            ->setParameter('userId', $userId)
            ->orderBy('c.createdAt', 'DESC')
            ->setMaxResults($limit)
            ->getQuery()
            ->getResult();
    }

    public function findStarredByUserId(int $userId): array
    {
        return $this->createQueryBuilder('c')
            ->andWhere('c.userId = :userId')
            ->andWhere('c.isStarred = :starred')
            ->setParameter('userId', $userId)
            ->setParameter('starred', true)
            ->orderBy('c.createdAt', 'DESC')
            ->getQuery()
            ->getResult();
    }

    public function findByIntent(int $userId, string $intent): array
    {
        return $this->createQueryBuilder('c')
            ->andWhere('c.userId = :userId')
            ->andWhere('c.intent = :intent')
            ->setParameter('userId', $userId)
            ->setParameter('intent', $intent)
            ->orderBy('c.createdAt', 'DESC')
            ->getQuery()
            ->getResult();
    }

    public function getConversationHistory(int $userId, int $limit = 20): array
    {
        $results = $this->createQueryBuilder('c')
            ->andWhere('c.userId = :userId')
            ->setParameter('userId', $userId)
            ->orderBy('c.createdAt', 'ASC')
            ->setMaxResults($limit)
            ->getQuery()
            ->getResult();
        
        // Reverse to get chronological order
        return array_reverse($results);
    }

    public function deleteOldConversations(int $userId, int $daysToKeep = 30): int
    {
        $cutoffDate = new \DateTime();
        $cutoffDate->modify("-{$daysToKeep} days");

        return $this->createQueryBuilder('c')
            ->delete()
            ->andWhere('c.userId = :userId')
            ->andWhere('c.createdAt < :cutoffDate')
            ->andWhere('c.isStarred = :starred')
            ->setParameter('userId', $userId)
            ->setParameter('cutoffDate', $cutoffDate)
            ->setParameter('starred', false)
            ->getQuery()
            ->execute();
    }
}
