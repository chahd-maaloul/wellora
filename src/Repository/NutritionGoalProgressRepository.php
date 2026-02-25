<?php

namespace App\Repository;

use App\Entity\NutritionGoalProgress;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<NutritionGoalProgress>
 */
class NutritionGoalProgressRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, NutritionGoalProgress::class);
    }

    /**
     * Find progress records by goal ID
     */
    public function findByGoalId(int $goalId): array
    {
        return $this->createQueryBuilder('p')
            ->andWhere('p.goal = :goalId')
            ->setParameter('goalId', $goalId)
            ->orderBy('p.date', 'DESC')
            ->getQuery()
            ->getResult();
    }

    /**
     * Find progress records for a goal within a date range
     */
    public function findByGoalIdAndDateRange(int $goalId, \DateTimeInterface $startDate, \DateTimeInterface $endDate): array
    {
        return $this->createQueryBuilder('p')
            ->andWhere('p.goal = :goalId')
            ->andWhere('p.date >= :startDate')
            ->andWhere('p.date <= :endDate')
            ->setParameter('goalId', $goalId)
            ->setParameter('startDate', $startDate)
            ->setParameter('endDate', $endDate)
            ->orderBy('p.date', 'ASC')
            ->getQuery()
            ->getResult();
    }

    /**
     * Find today's progress for a goal
     */
    public function findTodayByGoalId(int $goalId): ?NutritionGoalProgress
    {
        return $this->createQueryBuilder('p')
            ->andWhere('p.goal = :goalId')
            ->andWhere('p.date = :today')
            ->setParameter('goalId', $goalId)
            ->setParameter('today', new \DateTime())
            ->getQuery()
            ->getOneOrNullResult();
    }

    /**
     * Find the latest progress record for a goal
     */
    public function findLatestByGoalId(int $goalId): ?NutritionGoalProgress
    {
        return $this->createQueryBuilder('p')
            ->andWhere('p.goal = :goalId')
            ->setParameter('goalId', $goalId)
            ->orderBy('p.date', 'DESC')
            ->setMaxResults(1)
            ->getQuery()
            ->getOneOrNullResult();
    }

    /**
     * Calculate average adherence for a goal over a period
     */
    public function calculateAverageAdherence(int $goalId, int $days = 30): ?float
    {
        $result = $this->createQueryBuilder('p')
            ->select('AVG(p.adherence)')
            ->andWhere('p.goal = :goalId')
            ->andWhere('p.date >= :startDate')
            ->setParameter('goalId', $goalId)
            ->setParameter('startDate', new \DateTime("-{$days} days"))
            ->getQuery()
            ->getSingleScalarResult();

        return $result;
    }

    /**
     * Count total tracking days for a goal
     */
    public function countTrackingDays(int $goalId): int
    {
        return $this->createQueryBuilder('p')
            ->select('COUNT(p.id)')
            ->andWhere('p.goal = :goalId')
            ->setParameter('goalId', $goalId)
            ->getQuery()
            ->getSingleScalarResult();
    }
}
