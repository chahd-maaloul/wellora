<?php

namespace App\Repository;

use App\Entity\MealPlan;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<MealPlan>
 */
class MealPlanRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, MealPlan::class);
    }

    public function findByUserId(int $userId): array
    {
        return $this->createQueryBuilder('m')
            ->andWhere('m.userId = :userId')
            ->setParameter('userId', $userId)
            ->orderBy('m.date', 'ASC')
            ->addOrderBy('m.mealType', 'ASC')
            ->getQuery()
            ->getResult();
    }

    public function findByUserIdAndDateRange(int $userId, \DateTimeInterface $startDate, \DateTimeInterface $endDate): array
    {
        return $this->createQueryBuilder('m')
            ->andWhere('m.userId = :userId')
            ->setParameter('userId', $userId)
            ->andWhere('m.date >= :startDate')
            ->setParameter('startDate', $startDate)
            ->andWhere('m.date <= :endDate')
            ->setParameter('endDate', $endDate)
            ->orderBy('m.date', 'ASC')
            ->addOrderBy('m.mealType', 'ASC')
            ->getQuery()
            ->getResult();
    }

    public function findByUserIdAndDate(int $userId, \DateTimeInterface $date): array
    {
        return $this->createQueryBuilder('m')
            ->andWhere('m.userId = :userId')
            ->setParameter('userId', $userId)
            ->andWhere('m.date = :date')
            ->setParameter('date', $date->format('Y-m-d'))
            ->orderBy('m.mealType', 'ASC')
            ->getQuery()
            ->getResult();
    }

    public function findRecentByUserId(int $userId, int $limit = 10): array
    {
        return $this->createQueryBuilder('m')
            ->andWhere('m.userId = :userId')
            ->setParameter('userId', $userId)
            ->orderBy('m.generatedAt', 'DESC')
            ->setMaxResults($limit)
            ->getQuery()
            ->getResult();
    }

    public function getWeeklyPlanByUserId(int $userId): array
    {
        $today = new \DateTime();
        $weekStart = (clone $today)->modify('monday this week');
        $weekEnd = (clone $today)->modify('sunday this week');

        return $this->findByUserIdAndDateRange($userId, $weekStart, $weekEnd);
    }

    public function getMealStatsByUserId(int $userId): array
    {
        $qb = $this->createQueryBuilder('m')
            ->select('SUM(m.calories) as totalCalories, SUM(m.protein) as totalProtein, SUM(m.carbs) as totalCarbs, SUM(m.fats) as totalFats, COUNT(m.id) as totalMeals')
            ->andWhere('m.userId = :userId')
            ->setParameter('userId', $userId);

        $result = $qb->getQuery()->getSingleResult();
        
        return [
            'totalCalories' => $result['totalCalories'] ?? 0,
            'totalProtein' => $result['totalProtein'] ?? 0,
            'totalCarbs' => $result['totalCarbs'] ?? 0,
            'totalFats' => $result['totalFats'] ?? 0,
            'totalMeals' => $result['totalMeals'] ?? 0,
        ];
    }
}
