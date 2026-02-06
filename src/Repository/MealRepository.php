<?php

namespace App\Repository;

use App\Entity\Meal;
use App\Entity\User;
use DateTime;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Meal>
 */
class MealRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Meal::class);
    }

    /**
     * Find meals for a user on a specific date
     */
    public function findByUserAndDate(User $user, DateTime $date): array
    {
        return $this->createQueryBuilder('m')
            ->andWhere('m.user = :user')
            ->andWhere('m.mealDate = :date')
            ->andWhere('m.isDeleted = false')
            ->setParameter('user', $user)
            ->setParameter('date', $date)
            ->orderBy('m.scheduledTime', 'ASC')
            ->getQuery()
            ->getResult();
    }

    /**
     * Find meals by user, date range and meal type
     */
    public function findByUserDateRangeAndMealType(User $user, DateTime $startDate, DateTime $endDate, ?string $mealType = null): array
    {
        $qb = $this->createQueryBuilder('m')
            ->andWhere('m.user = :user')
            ->andWhere('m.mealDate >= :startDate')
            ->andWhere('m.mealDate <= :endDate')
            ->andWhere('m.isDeleted = false')
            ->setParameter('user', $user)
            ->setParameter('startDate', $startDate)
            ->setParameter('endDate', $endDate);

        if ($mealType !== null) {
            $qb->andWhere('m.mealType = :mealType')
               ->setParameter('mealType', $mealType);
        }

        return $qb->orderBy('m.mealDate', 'DESC')
                  ->addOrderBy('m.scheduledTime', 'ASC')
                  ->getQuery()
                  ->getResult();
    }

    /**
     * Find a specific meal by ID
     */
    public function findById(int $id): ?Meal
    {
        return $this->createQueryBuilder('m')
            ->andWhere('m.id = :id')
            ->andWhere('m.isDeleted = false')
            ->setParameter('id', $id)
            ->getQuery()
            ->getOneOrNullResult();
    }

    /**
     * Find incomplete meals for a user
     */
    public function findIncompleteMeals(User $user, DateTime $date): array
    {
        return $this->createQueryBuilder('m')
            ->andWhere('m.user = :user')
            ->andWhere('m.mealDate = :date')
            ->andWhere('m.isCompleted = false')
            ->andWhere('m.isDeleted = false')
            ->setParameter('user', $user)
            ->setParameter('date', $date)
            ->orderBy('m.scheduledTime', 'ASC')
            ->getQuery()
            ->getResult();
    }

    /**
     * Calculate meal counts by type for a date range
     */
    public function calculateMealCountsByType(User $user, DateTime $startDate, DateTime $endDate): array
    {
        $result = $this->createQueryBuilder('m')
            ->select('m.mealType, COUNT(m.id) as count')
            ->andWhere('m.user = :user')
            ->andWhere('m.mealDate >= :startDate')
            ->andWhere('m.mealDate <= :endDate')
            ->andWhere('m.isDeleted = false')
            ->setParameter('user', $user)
            ->setParameter('startDate', $startDate)
            ->setParameter('endDate', $endDate)
            ->groupBy('m.mealType')
            ->getQuery()
            ->getResult();

        $counts = [];
        foreach ($result as $row) {
            $counts[$row['mealType']] = $row['count'];
        }

        return $counts;
    }
}
