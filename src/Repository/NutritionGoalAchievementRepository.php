<?php

namespace App\Repository;

use App\Entity\NutritionGoalAchievement;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<NutritionGoalAchievement>
 */
class NutritionGoalAchievementRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, NutritionGoalAchievement::class);
    }

    /**
     * Find achievements by goal ID
     */
    public function findByGoalId(int $goalId): array
    {
        return $this->createQueryBuilder('a')
            ->andWhere('a.goal = :goalId')
            ->setParameter('goalId', $goalId)
            ->orderBy('a.unlockedAt', 'DESC')
            ->getQuery()
            ->getResult();
    }

    /**
     * Find unlocked achievements for a goal
     */
    public function findUnlockedByGoalId(int $goalId): array
    {
        return $this->createQueryBuilder('a')
            ->andWhere('a.goal = :goalId')
            ->andWhere('a.unlocked = :unlocked')
            ->setParameter('goalId', $goalId)
            ->setParameter('unlocked', true)
            ->orderBy('a.unlockedAt', 'DESC')
            ->getQuery()
            ->getResult();
    }

    /**
     * Find locked achievements for a goal
     */
    public function findLockedByGoalId(int $goalId): array
    {
        return $this->createQueryBuilder('a')
            ->andWhere('a.goal = :goalId')
            ->andWhere('a.unlocked = :unlocked')
            ->setParameter('goalId', $goalId)
            ->setParameter('unlocked', false)
            ->orderBy('a.points', 'DESC')
            ->getQuery()
            ->getResult();
    }

    /**
     * Count unlocked achievements for a goal
     */
    public function countUnlockedByGoalId(int $goalId): int
    {
        return $this->createQueryBuilder('a')
            ->select('COUNT(a.id)')
            ->andWhere('a.goal = :goalId')
            ->andWhere('a.unlocked = :unlocked')
            ->setParameter('goalId', $goalId)
            ->setParameter('unlocked', true)
            ->getQuery()
            ->getSingleScalarResult();
    }

    /**
     * Calculate total points for a goal
     */
    public function calculateTotalPointsByGoalId(int $goalId): int
    {
        $result = $this->createQueryBuilder('a')
            ->select('SUM(a.points)')
            ->andWhere('a.goal = :goalId')
            ->andWhere('a.unlocked = :unlocked')
            ->setParameter('goalId', $goalId)
            ->setParameter('unlocked', true)
            ->getQuery()
            ->getSingleScalarResult();

        return $result ?? 0;
    }

    /**
     * Find achievements by type for a goal
     */
    public function findByGoalIdAndType(int $goalId, string $type): array
    {
        return $this->createQueryBuilder('a')
            ->andWhere('a.goal = :goalId')
            ->andWhere('a.type = :type')
            ->setParameter('goalId', $goalId)
            ->setParameter('type', $type)
            ->getQuery()
            ->getResult();
    }
}
