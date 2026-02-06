<?php

namespace App\Repository;

use App\Entity\NutritionGoal;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<NutritionGoal>
 */
class NutritionGoalRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, NutritionGoal::class);
    }

    public function findActiveGoalsByUser(int $userId): array
    {
        return $this->createQueryBuilder('n')
            ->andWhere('n.user = :userId')
            ->andWhere('n.status = :status')
            ->setParameter('userId', $userId)
            ->setParameter('status', NutritionGoal::STATUS_ACTIVE)
            ->orderBy('n.priority', 'DESC')
            ->addOrderBy('n.createdAt', 'DESC')
            ->getQuery()
            ->getResult();
    }

    public function findAllGoalsByUser(int $userId): array
    {
        return $this->createQueryBuilder('n')
            ->andWhere('n.user = :userId')
            ->setParameter('userId', $userId)
            ->orderBy('n.createdAt', 'DESC')
            ->getQuery()
            ->getResult();
    }

    public function findGoalByUserAndId(int $userId, int $goalId): ?NutritionGoal
    {
        return $this->createQueryBuilder('n')
            ->andWhere('n.user = :userId')
            ->andWhere('n.id = :goalId')
            ->setParameter('userId', $userId)
            ->setParameter('goalId', $goalId)
            ->getQuery()
            ->getOneOrNullResult();
    }

    public function findGoalsByStatus(string $status): array
    {
        return $this->createQueryBuilder('n')
            ->andWhere('n.status = :status')
            ->setParameter('status', $status)
            ->orderBy('n.createdAt', 'DESC')
            ->getQuery()
            ->getResult();
    }

    public function findGoalsByType(string $goalType): array
    {
        return $this->createQueryBuilder('n')
            ->andWhere('n.goalType = :goalType')
            ->setParameter('goalType', $goalType)
            ->orderBy('n.createdAt', 'DESC')
            ->getQuery()
            ->getResult();
    }

    public function findOverdueGoals(): array
    {
        return $this->createQueryBuilder('n')
            ->andWhere('n.status = :status')
            ->andWhere('n.targetDate < :today')
            ->setParameter('status', NutritionGoal::STATUS_ACTIVE)
            ->setParameter('today', new \DateTime())
            ->getQuery()
            ->getResult();
    }

    public function findGoalsNeedingAdjustment(): array
    {
        return $this->createQueryBuilder('n')
            ->andWhere('n.status = :status')
            ->setParameter('status', NutritionGoal::STATUS_ACTIVE)
            ->getQuery()
            ->getResult();
    }

    public function countActiveGoalsByUser(int $userId): int
    {
        return $this->createQueryBuilder('n')
            ->select('COUNT(n.id)')
            ->andWhere('n.user = :userId')
            ->andWhere('n.status = :status')
            ->setParameter('userId', $userId)
            ->setParameter('status', NutritionGoal::STATUS_ACTIVE)
            ->getQuery()
            ->getSingleScalarResult();
    }

    public function findRecentGoalsByUser(int $userId, int $limit = 5): array
    {
        return $this->createQueryBuilder('n')
            ->andWhere('n.user = :userId')
            ->setParameter('userId', $userId)
            ->orderBy('n.createdAt', 'DESC')
            ->setMaxResults($limit)
            ->getQuery()
            ->getResult();
    }
}
