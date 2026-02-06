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

    public function findByGoal(int $goalId): array
    {
        return $this->createQueryBuilder('a')
            ->andWhere('a.nutritionGoal = :goalId')
            ->setParameter('goalId', $goalId)
            ->orderBy('a.earnedAt', 'DESC')
            ->getQuery()
            ->getResult();
    }

    public function findRecentAchievements(int $goalId, int $limit = 10): array
    {
        return $this->createQueryBuilder('a')
            ->andWhere('a.nutritionGoal = :goalId')
            ->setParameter('goalId', $goalId)
            ->orderBy('a.earnedAt', 'DESC')
            ->setMaxResults($limit)
            ->getQuery()
            ->getResult();
    }

    public function findByType(int $goalId, string $type): array
    {
        return $this->createQueryBuilder('a')
            ->andWhere('a.nutritionGoal = :goalId')
            ->andWhere('a.achievementType = :type')
            ->setParameter('goalId', $goalId)
            ->setParameter('type', $type)
            ->orderBy('a.earnedAt', 'DESC')
            ->getQuery()
            ->getResult();
    }

    public function findByTier(int $goalId, string $tier): array
    {
        return $this->createQueryBuilder('a')
            ->andWhere('a.nutritionGoal = :goalId')
            ->andWhere('a.tier = :tier')
            ->setParameter('goalId', $goalId)
            ->setParameter('tier', $tier)
            ->orderBy('a.earnedAt', 'DESC')
            ->getQuery()
            ->getResult();
    }

    public function countByGoal(int $goalId): int
    {
        return $this->createQueryBuilder('a')
            ->select('COUNT(a.id)')
            ->andWhere('a.nutritionGoal = :goalId')
            ->setParameter('goalId', $goalId)
            ->getQuery()
            ->getSingleScalarResult();
    }

    public function getTotalPointsByGoal(int $goalId): int
    {
        $result = $this->createQueryBuilder('a')
            ->select('SUM(a.points)')
            ->andWhere('a.nutritionGoal = :goalId')
            ->setParameter('goalId', $goalId)
            ->getQuery()
            ->getSingleScalarResult();

        return (int) ($result ?? 0);
    }

    public function findLatestByGoal(int $goalId, int $limit = 5): array
    {
        return $this->createQueryBuilder('a')
            ->andWhere('a.nutritionGoal = :goalId')
            ->setParameter('goalId', $goalId)
            ->orderBy('a.earnedAt', 'DESC')
            ->setMaxResults($limit)
            ->getQuery()
            ->getResult();
    }

    public function findUnsharedAchievements(int $goalId): array
    {
        return $this->createQueryBuilder('a')
            ->andWhere('a.nutritionGoal = :goalId')
            ->andWhere('a.isShared = false')
            ->setParameter('goalId', $goalId)
            ->orderBy('a.earnedAt', 'DESC')
            ->getQuery()
            ->getResult();
    }
}
