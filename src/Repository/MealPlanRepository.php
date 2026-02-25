<?php

namespace App\Repository;

use App\Entity\MealPlan;
use App\Entity\User;
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

    /**
     * Find meal plans by user UUID
     */
    public function findByUserUuid(string $userUuid): array
    {
        return $this->createQueryBuilder('m')
            ->andWhere('m.user = :userUuid')
            ->setParameter('userUuid', $userUuid)
            ->orderBy('m.date', 'ASC')
            ->addOrderBy('m.mealType', 'ASC')
            ->getQuery()
            ->getResult();
    }

    /**
     * Find meal plans by User entity
     */
    public function findByUser(User $user): array
    {
        return $this->createQueryBuilder('m')
            ->andWhere('m.user = :user')
            ->setParameter('user', $user)
            ->orderBy('m.date', 'ASC')
            ->addOrderBy('m.mealType', 'ASC')
            ->getQuery()
            ->getResult();
    }

    /**
     * Find meal plans by user UUID and date range
     */
    public function findByUserUuidAndDateRange(string $userUuid, \DateTimeInterface $startDate, \DateTimeInterface $endDate): array
    {
        return $this->createQueryBuilder('m')
            ->andWhere('m.user = :userUuid')
            ->setParameter('userUuid', $userUuid)
            ->andWhere('m.date >= :startDate')
            ->setParameter('startDate', $startDate)
            ->andWhere('m.date <= :endDate')
            ->setParameter('endDate', $endDate)
            ->orderBy('m.date', 'ASC')
            ->addOrderBy('m.mealType', 'ASC')
            ->getQuery()
            ->getResult();
    }

    /**
     * Find meal plans by User entity and date range
     */
    public function findByUserAndDateRange(User $user, \DateTimeInterface $startDate, \DateTimeInterface $endDate): array
    {
        return $this->createQueryBuilder('m')
            ->andWhere('m.user = :user')
            ->setParameter('user', $user)
            ->andWhere('m.date >= :startDate')
            ->setParameter('startDate', $startDate)
            ->andWhere('m.date <= :endDate')
            ->setParameter('endDate', $endDate)
            ->orderBy('m.date', 'ASC')
            ->addOrderBy('m.mealType', 'ASC')
            ->getQuery()
            ->getResult();
    }

    /**
     * Find meal plans by user UUID and date
     */
    public function findByUserUuidAndDate(string $userUuid, \DateTimeInterface $date): array
    {
        return $this->createQueryBuilder('m')
            ->andWhere('m.user = :userUuid')
            ->setParameter('userUuid', $userUuid)
            ->andWhere('m.date = :date')
            ->setParameter('date', $date->format('Y-m-d'))
            ->orderBy('m.mealType', 'ASC')
            ->getQuery()
            ->getResult();
    }

    /**
     * Find meal plans by User entity and date
     */
    public function findByUserAndDate(User $user, \DateTimeInterface $date): array
    {
        return $this->createQueryBuilder('m')
            ->andWhere('m.user = :user')
            ->setParameter('user', $user)
            ->andWhere('m.date = :date')
            ->setParameter('date', $date->format('Y-m-d'))
            ->orderBy('m.mealType', 'ASC')
            ->getQuery()
            ->getResult();
    }

    /**
     * Find recent meal plans by user UUID
     */
    public function findRecentByUserUuid(string $userUuid, int $limit = 10): array
    {
        return $this->createQueryBuilder('m')
            ->andWhere('m.user = :userUuid')
            ->setParameter('userUuid', $userUuid)
            ->orderBy('m.generatedAt', 'DESC')
            ->setMaxResults($limit)
            ->getQuery()
            ->getResult();
    }

    /**
     * Find recent meal plans by User entity
     */
    public function findRecentByUser(User $user, int $limit = 10): array
    {
        return $this->createQueryBuilder('m')
            ->andWhere('m.user = :user')
            ->setParameter('user', $user)
            ->orderBy('m.generatedAt', 'DESC')
            ->setMaxResults($limit)
            ->getQuery()
            ->getResult();
    }

    /**
     * Get weekly plan by user UUID
     */
    public function getWeeklyPlanByUserUuid(string $userUuid): array
    {
        $today = new \DateTime();
        $weekStart = (clone $today)->modify('monday this week');
        $weekEnd = (clone $today)->modify('sunday this week');

        return $this->findByUserUuidAndDateRange($userUuid, $weekStart, $weekEnd);
    }

    /**
     * Get weekly plan by User entity
     */
    public function getWeeklyPlanByUser(User $user): array
    {
        $today = new \DateTime();
        $weekStart = (clone $today)->modify('monday this week');
        $weekEnd = (clone $today)->modify('sunday this week');

        return $this->findByUserAndDateRange($user, $weekStart, $weekEnd);
    }

    /**
     * Get meal stats by user UUID
     */
    public function getMealStatsByUserUuid(string $userUuid): array
    {
        $qb = $this->createQueryBuilder('m')
            ->select('SUM(m.calories) as totalCalories, SUM(m.protein) as totalProtein, SUM(m.carbs) as totalCarbs, SUM(m.fats) as totalFats, COUNT(m.id) as totalMeals')
            ->andWhere('m.user = :userUuid')
            ->setParameter('userUuid', $userUuid);

        $result = $qb->getQuery()->getSingleResult();
        
        return [
            'totalCalories' => $result['totalCalories'] ?? 0,
            'totalProtein' => $result['totalProtein'] ?? 0,
            'totalCarbs' => $result['totalCarbs'] ?? 0,
            'totalFats' => $result['totalFats'] ?? 0,
            'totalMeals' => $result['totalMeals'] ?? 0,
        ];
    }

    /**
     * Get meal stats by User entity
     */
    public function getMealStatsByUser(User $user): array
    {
        $qb = $this->createQueryBuilder('m')
            ->select('SUM(m.calories) as totalCalories, SUM(m.protein) as totalProtein, SUM(m.carbs) as totalCarbs, SUM(m.fats) as totalFats, COUNT(m.id) as totalMeals')
            ->andWhere('m.user = :user')
            ->setParameter('user', $user);

        $result = $qb->getQuery()->getSingleResult();
        
        return [
            'totalCalories' => $result['totalCalories'] ?? 0,
            'totalProtein' => $result['totalProtein'] ?? 0,
            'totalCarbs' => $result['totalCarbs'] ?? 0,
            'totalFats' => $result['totalFats'] ?? 0,
            'totalMeals' => $result['totalMeals'] ?? 0,
        ];
    }

    // Legacy methods for backward compatibility (now uses UUID)

    /**
     * @deprecated Use findByUserUuid instead
     */
    public function findByUserId(string|int $userId): array
    {
        $userId = is_int($userId) ? (string) $userId : $userId;
        return $this->createQueryBuilder('m')
            ->andWhere('m.user = :userId')
            ->setParameter('userId', $userId)
            ->orderBy('m.date', 'ASC')
            ->addOrderBy('m.mealType', 'ASC')
            ->getQuery()
            ->getResult();
    }

    /**
     * @deprecated Use findByUserUuidAndDateRange instead
     */
    public function findByUserIdAndDateRange(string|int $userId, \DateTimeInterface $startDate, \DateTimeInterface $endDate): array
    {
        $userId = is_int($userId) ? (string) $userId : $userId;
        return $this->createQueryBuilder('m')
            ->andWhere('m.user = :userId')
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

    /**
     * @deprecated Use findByUserUuidAndDate instead
     */
    public function findByUserIdAndDate(string|int $userId, \DateTimeInterface $date): array
    {
        $userId = is_int($userId) ? (string) $userId : $userId;
        return $this->createQueryBuilder('m')
            ->andWhere('m.user = :userId')
            ->setParameter('userId', $userId)
            ->andWhere('m.date = :date')
            ->setParameter('date', $date->format('Y-m-d'))
            ->orderBy('m.mealType', 'ASC')
            ->getQuery()
            ->getResult();
    }

    /**
     * @deprecated Use findRecentByUserUuid instead
     */
    public function findRecentByUserId(string|int $userId, int $limit = 10): array
    {
        $userId = is_int($userId) ? (string) $userId : $userId;
        return $this->createQueryBuilder('m')
            ->andWhere('m.user = :userId')
            ->setParameter('userId', $userId)
            ->orderBy('m.generatedAt', 'DESC')
            ->setMaxResults($limit)
            ->getQuery()
            ->getResult();
    }

    /**
     * @deprecated Use getWeeklyPlanByUserUuid instead
     */
    public function getWeeklyPlanByUserId(string|int $userId): array
    {
        $userId = is_int($userId) ? (string) $userId : $userId;
        $today = new \DateTime();
        $weekStart = (clone $today)->modify('monday this week');
        $weekEnd = (clone $today)->modify('sunday this week');

        return $this->findByUserUuidAndDateRange($userId, $weekStart, $weekEnd);
    }

    /**
     * @deprecated Use getMealStatsByUserUuid instead
     */
    public function getMealStatsByUserId(string|int $userId): array
    {
        $userId = is_int($userId) ? (string) $userId : $userId;
        $qb = $this->createQueryBuilder('m')
            ->select('SUM(m.calories) as totalCalories, SUM(m.protein) as totalProtein, SUM(m.carbs) as totalCarbs, SUM(m.fats) as totalFats, COUNT(m.id) as totalMeals')
            ->andWhere('m.user = :userId')
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
