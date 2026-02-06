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

    public function findByGoal(int $goalId): array
    {
        return $this->createQueryBuilder('p')
            ->andWhere('p.nutritionGoal = :goalId')
            ->setParameter('goalId', $goalId)
            ->orderBy('p.date', 'DESC')
            ->getQuery()
            ->getResult();
    }

    public function findRecentProgressByGoal(int $goalId, int $days = 30): array
    {
        $date = new \DateTime();
        $date->modify("-{$days} days");

        return $this->createQueryBuilder('p')
            ->andWhere('p.nutritionGoal = :goalId')
            ->andWhere('p.date >= :date')
            ->setParameter('goalId', $goalId)
            ->setParameter('date', $date)
            ->orderBy('p.date', 'ASC')
            ->getQuery()
            ->getResult();
    }

    public function findByGoalAndDateRange(int $goalId, \DateTimeInterface $startDate, \DateTimeInterface $endDate): array
    {
        return $this->createQueryBuilder('p')
            ->andWhere('p.nutritionGoal = :goalId')
            ->andWhere('p.date >= :startDate')
            ->andWhere('p.date <= :endDate')
            ->setParameter('goalId', $goalId)
            ->setParameter('startDate', $startDate)
            ->setParameter('endDate', $endDate)
            ->orderBy('p.date', 'ASC')
            ->getQuery()
            ->getResult();
    }

    public function findLatestProgressByGoal(int $goalId): ?NutritionGoalProgress
    {
        return $this->createQueryBuilder('p')
            ->andWhere('p.nutritionGoal = :goalId')
            ->setParameter('goalId', $goalId)
            ->orderBy('p.date', 'DESC')
            ->setMaxResults(1)
            ->getQuery()
            ->getOneOrNullResult();
    }

    public function getAverageAdherenceScore(int $goalId, int $days = 7): ?string
    {
        $date = new \DateTime();
        $date->modify("-{$days} days");

        $result = $this->createQueryBuilder('p')
            ->select('AVG(p.adherenceScore)')
            ->andWhere('p.nutritionGoal = :goalId')
            ->andWhere('p.date >= :date')
            ->andWhere('p.adherenceScore IS NOT NULL')
            ->setParameter('goalId', $goalId)
            ->setParameter('date', $date)
            ->getQuery()
            ->getSingleScalarResult();

        return $result;
    }

    public function countGoalsMet(int $goalId, int $days = 30): int
    {
        $date = new \DateTime();
        $date->modify("-{$days} days");

        return $this->createQueryBuilder('p')
            ->select('COUNT(p.id)')
            ->andWhere('p.nutritionGoal = :goalId')
            ->andWhere('p.date >= :date')
            ->andWhere('p.goalsMet = true')
            ->setParameter('goalId', $goalId)
            ->setParameter('date', $date)
            ->getQuery()
            ->getSingleScalarResult();
    }
}
