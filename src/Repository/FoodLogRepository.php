<?php

namespace App\Repository;

use App\Entity\FoodLog;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<FoodLog>
 */
class FoodLogRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, FoodLog::class);
    }

    /**
     * Find food log by user ID and date
     */
    public function findByUserIdAndDate(int $userId, \DateTimeInterface $date): ?FoodLog
    {
        return $this->findOneBy([
            'userId' => $userId,
            'date' => $date
        ]);
    }

    /**
     * Find all food logs for a user on a specific date
     */
    public function findAllByUserIdAndDate(int $userId, \DateTimeInterface $date): array
    {
        return $this->findBy([
            'userId' => $userId,
            'date' => $date
        ]);
    }

    /**
     * Find all food logs for a user
     */
    public function findAllByUserId(int $userId): array
    {
        return $this->findBy(['userId' => $userId]);
    }

    /**
     * Find food logs by user ID and date range
     */
    public function findByUserIdAndDateRange(int $userId, \DateTimeInterface $startDate, \DateTimeInterface $endDate): array
    {
        return $this->createQueryBuilder('f')
            ->andWhere('f.userId = :userId')
            ->andWhere('f.date >= :startDate')
            ->andWhere('f.date <= :endDate')
            ->setParameter('userId', $userId)
            ->setParameter('startDate', $startDate)
            ->setParameter('endDate', $endDate)
            ->orderBy('f.date', 'ASC')
            ->getQuery()
            ->getResult();
    }

    /**
     * Find food logs by user ID and meal type
     */
    public function findByUserIdAndMealType(int $userId, string $mealType): array
    {
        return $this->findBy([
            'userId' => $userId,
            'mealType' => $mealType
        ]);
    }
}
