<?php

namespace App\Repository;

use App\Entity\AiConversation;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<AiConversation>
 *
 * @method AiConversation[] findByUserId(string $userId, int $limit = 50)
 * @method AiConversation[] findRecentByUserId(string $userId, int $limit = 10)
 * @method AiConversation[] findStarredByUserId(string $userId)
 * @method AiConversation[] findByIntent(string|int $userId, string $intent)
 * @method AiConversation[] getConversationHistory(string|int $userId, int $limit = 20)
 */
class AiConversationRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, AiConversation::class);
    }

    /**
     * Find conversations by user ID (accepts UUID string or integer)
     *
     * @param string|int $userId User UUID string or integer ID
     * @param int $limit Maximum number of results
     * @return AiConversation[]
     */
    public function findByUserId(string $userId, int $limit = 50): array
    {
        // Convert UUID to integer using same logic as entity
        $userIdInt = (int) hexdec(substr($userId, 0, 8));
        
        return $this->createQueryBuilder('c')
            ->andWhere('c.userId = :userId')
            ->setParameter('userId', $userIdInt)
            ->orderBy('c.createdAt', 'DESC')
            ->setMaxResults($limit)
            ->getQuery()
            ->getResult();
    }

    /**
     * Find recent conversations by user ID
     *
     * @param string|int $userId User UUID string or integer ID
     * @param int $limit Maximum number of results
     * @return AiConversation[]
     */
    public function findRecentByUserId(string $userId, int $limit = 10): array
    {
        // Convert UUID to integer using same logic as entity
        $userIdInt = (int) hexdec(substr($userId, 0, 8));
        
        return $this->createQueryBuilder('c')
            ->andWhere('c.userId = :userId')
            ->setParameter('userId', $userIdInt)
            ->orderBy('c.createdAt', 'DESC')
            ->setMaxResults($limit)
            ->getQuery()
            ->getResult();
    }

    /**
     * Find starred conversations by user ID
     *
     * @param string|int $userId User UUID string or integer ID
     * @return AiConversation[]
     */
    public function findStarredByUserId(string $userId): array
    {
        // Convert UUID to integer using same logic as entity
        $userIdInt = (int) hexdec(substr($userId, 0, 8));
        
        return $this->createQueryBuilder('c')
            ->andWhere('c.userId = :userId')
            ->andWhere('c.isStarred = :starred')
            ->setParameter('userId', $userIdInt)
            ->setParameter('starred', true)
            ->orderBy('c.createdAt', 'DESC')
            ->getQuery()
            ->getResult();
    }

    /**
     * Find conversations by intent
     *
     * @param string|int $userId User UUID string or integer ID
     * @param string $intent Intent to filter by
     * @return AiConversation[]
     */
    public function findByIntent(int|string $userId, string $intent): array
    {
        // Convert UUID to integer using same logic as entity
        $userIdInt = is_string($userId) ? (int) hexdec(substr($userId, 0, 8)) : $userId;
        
        return $this->createQueryBuilder('c')
            ->andWhere('c.userId = :userId')
            ->andWhere('c.intent = :intent')
            ->setParameter('userId', $userIdInt)
            ->setParameter('intent', $intent)
            ->orderBy('c.createdAt', 'DESC')
            ->getQuery()
            ->getResult();
    }

    /**
     * Get conversation history for a user
     *
     * @param string|int $userId User UUID string or integer ID
     * @param int $limit Maximum number of results
     * @return AiConversation[]
     */
    public function getConversationHistory(int|string $userId, int $limit = 20): array
    {
        // Convert UUID to integer using same logic as entity
        $userIdInt = is_string($userId) ? (int) hexdec(substr($userId, 0, 8)) : $userId;
        
        $results = $this->createQueryBuilder('c')
            ->andWhere('c.userId = :userId')
            ->setParameter('userId', $userIdInt)
            ->orderBy('c.createdAt', 'ASC')
            ->setMaxResults($limit)
            ->getQuery()
            ->getResult();
        
        // Reverse to get chronological order
        return array_reverse($results);
    }

    /**
     * Delete old conversations for a user
     *
     * @param string|int $userId User UUID string or integer ID
     * @param int $daysToKeep Number of days to keep
     * @return int Number of deleted records
     */
    public function deleteOldConversations(int|string $userId, int $daysToKeep = 30): int
    {
        // Convert UUID to integer using same logic as entity
        $userIdInt = is_string($userId) ? (int) hexdec(substr($userId, 0, 8)) : $userId;
        
        $cutoffDate = new \DateTime();
        $cutoffDate->modify("-{$daysToKeep} days");

        return $this->createQueryBuilder('c')
            ->delete()
            ->andWhere('c.userId = :userId')
            ->andWhere('c.createdAt < :cutoffDate')
            ->andWhere('c.isStarred = :starred')
            ->setParameter('userId', $userIdInt)
            ->setParameter('cutoffDate', $cutoffDate)
            ->setParameter('starred', false)
            ->getQuery()
            ->execute();
    }
}
