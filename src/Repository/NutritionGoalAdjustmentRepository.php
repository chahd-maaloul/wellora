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

    public function findByGoal(int $goalId): array
    {
        return $this->createQueryBuilder('a')
            ->andWhere('a.nutritionGoal = :goalId')
            ->setParameter('goalId', $goalId)
            ->orderBy('a.adjustmentDate', 'DESC')
            ->getQuery()
            ->getResult();
    }

    public function findActiveAdjustments(int $goalId): array
    {
        return $this->createQueryBuilder('a')
            ->andWhere('a.nutritionGoal = :goalId')
            ->andWhere('a.isActive = true')
            ->setParameter('goalId', $goalId)
            ->orderBy('a.adjustmentDate', 'DESC')
            ->getQuery()
            ->getResult();
    }

    public function findLatestAdjustment(int $goalId): ?NutritionGoalAdjustment
    {
        return $this->createQueryBuilder('a')
            ->andWhere('a.nutritionGoal = :goalId')
            ->andWhere('a.isActive = true')
            ->setParameter('goalId', $goalId)
            ->orderBy('a.adjustmentDate', 'DESC')
            ->setMaxResults(1)
            ->getQuery()
            ->getOneOrNullResult();
    }

    public function findAdjustmentsByType(int $goalId, string $type): array
    {
        return $this->createQueryBuilder('a')
            ->andWhere('a.nutritionGoal = :goalId')
            ->andWhere('a.adjustmentType = :type')
            ->setParameter('goalId', $goalId)
            ->setParameter('type', $type)
            ->orderBy('a.adjustmentDate', 'DESC')
            ->getQuery()
            ->getResult();
    }

    public function countAdjustmentsByGoal(int $goalId): int
    {
        return $this->createQueryBuilder('a')
            ->select('COUNT(a.id)')
            ->andWhere('a.nutritionGoal = :goalId')
            ->setParameter('goalId', $goalId)
            ->getQuery()
            ->getSingleScalarResult();
    }
}
