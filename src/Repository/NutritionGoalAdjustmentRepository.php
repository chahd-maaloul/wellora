<?php

namespace App\Repository;

use App\Entity\NutritionGoalAdjustment;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<NutritionGoalAdjustment>
 */
class NutritionGoalAdjustmentRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, NutritionGoalAdjustment::class);
    }

    /**
     * Find adjustments by goal ID, ordered by creation date
     */
    public function findByGoalIdOrdered(int $goalId): array
    {
        return $this->createQueryBuilder('a')
            ->andWhere('a.goal = :goalId')
            ->setParameter('goalId', $goalId)
            ->orderBy('a.createdAt', 'DESC')
            ->getQuery()
            ->getResult();
    }

    /**
     * Find active adjustment for a goal
     */
    public function findActiveByGoalId(int $goalId): ?NutritionGoalAdjustment
    {
        return $this->createQueryBuilder('a')
            ->andWhere('a.goal = :goalId')
            ->andWhere('a.isActive = :active')
            ->setParameter('goalId', $goalId)
            ->setParameter('active', true)
            ->orderBy('a.createdAt', 'DESC')
            ->setMaxResults(1)
            ->getQuery()
            ->getOneOrNullResult();
    }

    /**
     * Find latest adjustment for a goal
     */
    public function findLatestByGoalId(int $goalId): ?NutritionGoalAdjustment
    {
        return $this->createQueryBuilder('a')
            ->andWhere('a.goal = :goalId')
            ->setParameter('goalId', $goalId)
            ->orderBy('a.createdAt', 'DESC')
            ->setMaxResults(1)
            ->getQuery()
            ->getOneOrNullResult();
    }

    /**
     * Find adjustments by type for a goal
     */
    public function findByGoalIdAndType(int $goalId, string $type): array
    {
        return $this->createQueryBuilder('a')
            ->andWhere('a.goal = :goalId')
            ->andWhere('a.adjustmentType = :type')
            ->setParameter('goalId', $goalId)
            ->setParameter('type', $type)
            ->orderBy('a.createdAt', 'DESC')
            ->getQuery()
            ->getResult();
    }

    /**
     * Count adjustments for a goal
     */
    public function countByGoalId(int $goalId): int
    {
        return $this->createQueryBuilder('a')
            ->select('COUNT(a.id)')
            ->andWhere('a.goal = :goalId')
            ->setParameter('goalId', $goalId)
            ->getQuery()
            ->getSingleScalarResult();
    }
}
