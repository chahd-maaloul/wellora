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

    public function findByGoal(int $goalId): array
    {
        return $this->createQueryBuilder('m')
            ->andWhere('m.nutritionGoal = :goalId')
            ->setParameter('goalId', $goalId)
            ->orderBy('m.order', 'ASC')
            ->addOrderBy('m.targetDate', 'ASC')
            ->getQuery()
            ->getResult();
    }

    public function findCompletedByGoal(int $goalId): array
    {
        return $this->createQueryBuilder('m')
            ->andWhere('m.nutritionGoal = :goalId')
            ->andWhere('m.isCompleted = true')
            ->setParameter('goalId', $goalId)
            ->orderBy('m.completedDate', 'DESC')
            ->getQuery()
            ->getResult();
    }

    public function findPendingByGoal(int $goalId): array
    {
        return $this->createQueryBuilder('m')
            ->andWhere('m.nutritionGoal = :goalId')
            ->andWhere('m.isCompleted = false')
            ->setParameter('goalId', $goalId)
            ->orderBy('m.targetDate', 'ASC')
            ->getQuery()
            ->getResult();
    }

    public function findOverdueMilestones(int $goalId): array
    {
        return $this->createQueryBuilder('m')
            ->andWhere('m.nutritionGoal = :goalId')
            ->andWhere('m.isCompleted = false')
            ->andWhere('m.targetDate < :today')
            ->setParameter('goalId', $goalId)
            ->setParameter('today', new \DateTime())
            ->orderBy('m.targetDate', 'ASC')
            ->getQuery()
            ->getResult();
    }

    public function countCompletedByGoal(int $goalId): int
    {
        return $this->createQueryBuilder('m')
            ->select('COUNT(m.id)')
            ->andWhere('m.nutritionGoal = :goalId')
            ->andWhere('m.isCompleted = true')
            ->setParameter('goalId', $goalId)
            ->getQuery()
            ->getSingleScalarResult();
    }

    public function findUpcomingMilestones(int $goalId, int $days = 7): array
    {
        $date = new \DateTime();
        $endDate = clone $date;
        $endDate->modify("+{$days} days");

        return $this->createQueryBuilder('m')
            ->andWhere('m.nutritionGoal = :goalId')
            ->andWhere('m.isCompleted = false')
            ->andWhere('m.targetDate >= :startDate')
            ->andWhere('m.targetDate <= :endDate')
            ->setParameter('goalId', $goalId)
            ->setParameter('startDate', $date)
            ->setParameter('endDate', $endDate)
            ->orderBy('m.targetDate', 'ASC')
            ->getQuery()
            ->getResult();
    }
}
