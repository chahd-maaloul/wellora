<?php

namespace App\Repository;

use App\Entity\NutritionGoalMilestone;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<NutritionGoalMilestone>
 */
class NutritionGoalMilestoneRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, NutritionGoalMilestone::class);
    }

    /**
     * Find milestones by goal ID, ordered by order
     */
    public function findByGoalIdOrdered(int $goalId): array
    {
        return $this->createQueryBuilder('m')
            ->andWhere('m.goal = :goalId')
            ->setParameter('goalId', $goalId)
            ->orderBy('m.order', 'ASC')
            ->addOrderBy('m.id', 'ASC')
            ->getQuery()
            ->getResult();
    }

    /**
     * Find completed milestones for a goal
     */
    public function findCompletedByGoalId(int $goalId): array
    {
        return $this->createQueryBuilder('m')
            ->andWhere('m.goal = :goalId')
            ->andWhere('m.completed = :completed')
            ->setParameter('goalId', $goalId)
            ->setParameter('completed', true)
            ->orderBy('m.completedAt', 'DESC')
            ->getQuery()
            ->getResult();
    }

    /**
     * Find pending milestones for a goal
     */
    public function findPendingByGoalId(int $goalId): array
    {
        return $this->createQueryBuilder('m')
            ->andWhere('m.goal = :goalId')
            ->andWhere('m.completed = :completed')
            ->setParameter('goalId', $goalId)
            ->setParameter('completed', false)
            ->orderBy('m.order', 'ASC')
            ->getQuery()
            ->getResult();
    }

    /**
     * Find next pending milestone for a goal
     */
    public function findNextPendingByGoalId(int $goalId): ?NutritionGoalMilestone
    {
        return $this->createQueryBuilder('m')
            ->andWhere('m.goal = :goalId')
            ->andWhere('m.completed = :completed')
            ->setParameter('goalId', $goalId)
            ->setParameter('completed', false)
            ->orderBy('m.order', 'ASC')
            ->setMaxResults(1)
            ->getQuery()
            ->getOneOrNullResult();
    }

    /**
     * Count completed milestones for a goal
     */
    public function countCompletedByGoalId(int $goalId): int
    {
        return $this->createQueryBuilder('m')
            ->select('COUNT(m.id)')
            ->andWhere('m.goal = :goalId')
            ->andWhere('m.completed = :completed')
            ->setParameter('goalId', $goalId)
            ->setParameter('completed', true)
            ->getQuery()
            ->getSingleScalarResult();
    }
}
