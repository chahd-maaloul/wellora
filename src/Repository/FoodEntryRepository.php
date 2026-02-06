<?php

namespace App\Repository;

use App\Entity\FoodEntry;
use App\Entity\User;
use DateTime;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<FoodEntry>
 */
class FoodEntryRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, FoodEntry::class);
    }

    /**
     * Find all entries for a user on a specific date
     */
    public function findByUserAndDate(User $user, DateTime $date): array
    {
        return $this->createQueryBuilder('f')
            ->andWhere('f.user = :user')
            ->andWhere('f.entryDate = :date')
            ->andWhere('f.isDeleted = false')
            ->setParameter('user', $user)
            ->setParameter('date', $date)
            ->orderBy('f.entryTime', 'ASC')
            ->getQuery()
            ->getResult();
    }

    /**
     * Find entries by user, date range and meal type
     */
    public function findByUserDateRangeAndMealType(User $user, DateTime $startDate, DateTime $endDate, ?string $mealType = null): array
    {
        $qb = $this->createQueryBuilder('f')
            ->andWhere('f.user = :user')
            ->andWhere('f.entryDate >= :startDate')
            ->andWhere('f.entryDate <= :endDate')
            ->andWhere('f.isDeleted = false')
            ->setParameter('user', $user)
            ->setParameter('startDate', $startDate)
            ->setParameter('endDate', $endDate);

        if ($mealType !== null) {
            $qb->andWhere('f.mealType = :mealType')
               ->setParameter('mealType', $mealType);
        }

        return $qb->orderBy('f.entryDate', 'DESC')
                  ->addOrderBy('f.entryTime', 'ASC')
                  ->getQuery()
                  ->getResult();
    }

    /**
     * Find recent/frequent foods for a user
     */
    public function findRecentFrequentFoods(User $user, int $limit = 10): array
    {
        return $this->createQueryBuilder('f')
            ->select('f.foodName, COUNT(f.id) as entryCount, SUM(f.calories) as totalCalories')
            ->andWhere('f.user = :user')
            ->andWhere('f.isDeleted = false')
            ->setParameter('user', $user)
            ->groupBy('f.foodName')
            ->orderBy('entryCount', 'DESC')
            ->setMaxResults($limit)
            ->getQuery()
            ->getResult();
    }

    /**
     * Find most recent entries for autocomplete
     */
    public function findRecentFoodNames(User $user, int $limit = 20): array
    {
        return $this->createQueryBuilder('f')
            ->select('DISTINCT f.foodName')
            ->andWhere('f.user = :user')
            ->andWhere('f.isDeleted = false')
            ->setParameter('user', $user)
            ->orderBy('f.id', 'DESC')
            ->setMaxResults($limit)
            ->getQuery()
            ->getResult();
    }

    /**
     * Calculate daily totals for a user
     */
    public function calculateDailyTotals(User $user, DateTime $date): array
    {
        $result = $this->createQueryBuilder('f')
            ->select('SUM(f.calories) as calories, SUM(f.proteins) as proteins, SUM(f.carbohydrates) as carbohydrates, SUM(f.fats) as fats, SUM(f.fiber) as fiber, SUM(f.sugar) as sugar, SUM(f.sodium) as sodium')
            ->andWhere('f.user = :user')
            ->andWhere('f.entryDate = :date')
            ->andWhere('f.isDeleted = false')
            ->setParameter('user', $user)
            ->setParameter('date', $date)
            ->getQuery()
            ->getOneOrNullResult();

        return $result ?: [
            'calories' => 0,
            'proteins' => 0,
            'carbohydrates' => 0,
            'fats' => 0,
            'fiber' => 0,
            'sugar' => 0,
            'sodium' => 0,
        ];
    }

    /**
     * Find entries by barcode product
     */
    public function findByBarcodeProduct(int $barcodeProductId): array
    {
        return $this->createQueryBuilder('f')
            ->andWhere('f.barcodeProduct = :id')
            ->setParameter('id', $barcodeProductId)
            ->andWhere('f.isDeleted = false')
            ->orderBy('f.entryDate', 'DESC')
            ->getQuery()
            ->getResult();
    }

    /**
     * Search foods by name
     */
    public function searchByFoodName(User $user, string $searchTerm, int $limit = 20): array
    {
        return $this->createQueryBuilder('f')
            ->andWhere('f.user = :user')
            ->andWhere('f.foodName LIKE :search')
            ->andWhere('f.isDeleted = false')
            ->setParameter('user', $user)
            ->setParameter('search', '%' . $searchTerm . '%')
            ->setMaxResults($limit)
            ->getQuery()
            ->getResult();
    }
}
