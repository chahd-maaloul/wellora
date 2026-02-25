<?php

namespace App\Repository;

use App\Entity\FoodLog;
use App\Entity\User;
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
     * Find food log by user UUID and date
     */
    public function findByUserUuidAndDate(string $userUuid, \DateTimeInterface $date): ?FoodLog
    {
        return $this->findOneBy([
            'user' => $userUuid,
            'date' => $date
        ]);
    }

    /**
     * Find food log by User entity and date
     */
    public function findByUserAndDate(User $user, \DateTimeInterface $date): ?FoodLog
    {
        return $this->findOneBy([
            'user' => $user,
            'date' => $date
        ]);
    }

    /**
     * Find all food logs for a user UUID on a specific date
     */
    public function findAllByUserUuidAndDate(string $userUuid, \DateTimeInterface $date): array
    {
        return $this->findBy([
            'user' => $userUuid,
            'date' => $date
        ]);
    }

    /**
     * Find all food logs for a User entity on a specific date
     */
    public function findAllByUserAndDate(User $user, \DateTimeInterface $date): array
    {
        return $this->findBy([
            'user' => $user,
            'date' => $date
        ]);
    }

    /**
     * Find all food logs for a user UUID
     */
    public function findAllByUserUuid(string $userUuid): array
    {
        return $this->findBy(['user' => $userUuid]);
    }

    /**
     * Find all food logs for a User entity
     */
    public function findAllByUser(User $user): array
    {
        return $this->findBy(['user' => $user]);
    }

    /**
     * Find food logs by user UUID and date range
     */
    public function findByUserUuidAndDateRange(string $userUuid, \DateTimeInterface $startDate, \DateTimeInterface $endDate): array
    {
        return $this->createQueryBuilder('f')
            ->andWhere('f.user = :userUuid')
            ->andWhere('f.date >= :startDate')
            ->andWhere('f.date <= :endDate')
            ->setParameter('userUuid', $userUuid)
            ->setParameter('startDate', $startDate)
            ->setParameter('endDate', $endDate)
            ->orderBy('f.date', 'ASC')
            ->getQuery()
            ->getResult();
    }

    /**
     * Find food logs by User entity and date range
     */
    public function findByUserAndDateRange(User $user, \DateTimeInterface $startDate, \DateTimeInterface $endDate): array
    {
        return $this->createQueryBuilder('f')
            ->andWhere('f.user = :user')
            ->andWhere('f.date >= :startDate')
            ->andWhere('f.date <= :endDate')
            ->setParameter('user', $user)
            ->setParameter('startDate', $startDate)
            ->setParameter('endDate', $endDate)
            ->orderBy('f.date', 'ASC')
            ->getQuery()
            ->getResult();
    }

    /**
     * Find food logs by user UUID and meal type
     */
    public function findByUserUuidAndMealType(string $userUuid, string $mealType): array
    {
        return $this->findBy([
            'user' => $userUuid,
            'mealType' => $mealType
        ]);
    }

    /**
     * Find food logs by User entity and meal type
     */
    public function findByUserAndMealType(User $user, string $mealType): array
    {
        return $this->findBy([
            'user' => $user,
            'mealType' => $mealType
        ]);
    }

    // Legacy methods for backward compatibility (now uses UUID)
    
    /**
     * Find food log by user ID and date
     * @deprecated Use findByUserUuidAndDate instead
     */
    public function findByUserIdAndDate(string|int $userId, \DateTimeInterface $date): ?FoodLog
    {
        $userId = is_int($userId) ? (string) $userId : $userId;
        return $this->findOneBy([
            'user' => $userId,
            'date' => $date
        ]);
    }

    /**
     * Find all food logs for a user on a specific date
     * @deprecated Use findAllByUserUuidAndDate instead
     */
    public function findAllByUserIdAndDate(string|int $userId, \DateTimeInterface $date): array
    {
        $userId = is_int($userId) ? (string) $userId : $userId;
        return $this->findBy([
            'user' => $userId,
            'date' => $date
        ]);
    }

    /**
     * Find all food logs for a user
     * @deprecated Use findAllByUserUuid instead
     */
    public function findAllByUserId(string|int $userId): array
    {
        $userId = is_int($userId) ? (string) $userId : $userId;
        return $this->findBy(['user' => $userId]);
    }

    /**
     * Find food logs by user ID and date range
     * @deprecated Use findByUserUuidAndDateRange instead
     */
    public function findByUserIdAndDateRange(string|int $userId, \DateTimeInterface $startDate, \DateTimeInterface $endDate): array
    {
        $userId = is_int($userId) ? (string) $userId : $userId;
        return $this->createQueryBuilder('f')
            ->andWhere('f.user = :userId')
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
     * @deprecated Use findByUserUuidAndMealType instead
     */
    public function findByUserIdAndMealType(string|int $userId, string $mealType): array
    {
        $userId = is_int($userId) ? (string) $userId : $userId;
        return $this->findBy([
            'user' => $userId,
            'mealType' => $mealType
        ]);
    }
}
