<?php

namespace App\Repository;

use App\Entity\WaterIntake;
use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<WaterIntake>
 */
class WaterIntakeRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, WaterIntake::class);
    }

    /**
     * Find water intake by user UUID and date
     */
    public function findByUserUuidAndDate(string $userUuid, \DateTimeInterface $date): ?WaterIntake
    {
        return $this->findOneBy([
            'user' => $userUuid,
            'date' => $date
        ]);
    }

    /**
     * Find water intake by User entity and date
     */
    public function findByUserAndDate(User $user, \DateTimeInterface $date): ?WaterIntake
    {
        return $this->findOneBy([
            'user' => $user,
            'date' => $date
        ]);
    }

    /**
     * Find all water intakes for a user UUID on a specific date
     */
    public function findAllByUserUuidAndDate(string $userUuid, \DateTimeInterface $date): array
    {
        return $this->findBy([
            'user' => $userUuid,
            'date' => $date
        ]);
    }

    /**
     * Find all water intakes for a User entity on a specific date
     */
    public function findAllByUserAndDate(User $user, \DateTimeInterface $date): array
    {
        return $this->findBy([
            'user' => $user,
            'date' => $date
        ]);
    }

    /**
     * Find all water intakes for a user UUID
     */
    public function findAllByUserUuid(string $userUuid): array
    {
        return $this->findBy(['user' => $userUuid]);
    }

    /**
     * Find all water intakes for a User entity
     */
    public function findAllByUser(User $user): array
    {
        return $this->findBy(['user' => $user]);
    }

    /**
     * Find water intakes by user UUID and date range
     */
    public function findByUserUuidAndDateRange(string $userUuid, \DateTimeInterface $startDate, \DateTimeInterface $endDate): array
    {
        return $this->createQueryBuilder('w')
            ->andWhere('w.user = :userUuid')
            ->andWhere('w.date >= :startDate')
            ->andWhere('w.date <= :endDate')
            ->setParameter('userUuid', $userUuid)
            ->setParameter('startDate', $startDate)
            ->setParameter('endDate', $endDate)
            ->orderBy('w.date', 'ASC')
            ->getQuery()
            ->getResult();
    }

    /**
     * Find water intakes by User entity and date range
     */
    public function findByUserAndDateRange(User $user, \DateTimeInterface $startDate, \DateTimeInterface $endDate): array
    {
        return $this->createQueryBuilder('w')
            ->andWhere('w.user = :user')
            ->andWhere('w.date >= :startDate')
            ->andWhere('w.date <= :endDate')
            ->setParameter('user', $user)
            ->setParameter('startDate', $startDate)
            ->setParameter('endDate', $endDate)
            ->orderBy('w.date', 'ASC')
            ->getQuery()
            ->getResult();
    }

    /**
     * Get total water intake for a user UUID on a specific date
     */
    public function getTotalGlassesForUserUuidDate(string $userUuid, \DateTimeInterface $date): int
    {
        $result = $this->createQueryBuilder('w')
            ->select('SUM(w.glasses)')
            ->andWhere('w.user = :userUuid')
            ->andWhere('w.date = :date')
            ->setParameter('userUuid', $userUuid)
            ->setParameter('date', $date)
            ->getQuery()
            ->getSingleScalarResult();

        return $result ?? 0;
    }

    /**
     * Get total water intake for a User entity on a specific date
     */
    public function getTotalGlassesForUserDate(User $user, \DateTimeInterface $date): int
    {
        $result = $this->createQueryBuilder('w')
            ->select('SUM(w.glasses)')
            ->andWhere('w.user = :user')
            ->andWhere('w.date = :date')
            ->setParameter('user', $user)
            ->setParameter('date', $date)
            ->getQuery()
            ->getSingleScalarResult();

        return $result ?? 0;
    }

    // Legacy methods for backward compatibility (now uses UUID)

    /**
     * Find water intake by user ID and date
     * @deprecated Use findByUserUuidAndDate instead
     */
    public function findByUserIdAndDate(string|int $userId, \DateTimeInterface $date): ?WaterIntake
    {
        $userId = is_int($userId) ? (string) $userId : $userId;
        return $this->findOneBy([
            'user' => $userId,
            'date' => $date
        ]);
    }

    /**
     * Find all water intakes for a user on a specific date
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
     * Find all water intakes for a user
     * @deprecated Use findAllByUserUuid instead
     */
    public function findAllByUserId(string|int $userId): array
    {
        $userId = is_int($userId) ? (string) $userId : $userId;
        return $this->findBy(['user' => $userId]);
    }

    /**
     * Find water intakes by user ID and date range
     * @deprecated Use findByUserUuidAndDateRange instead
     */
    public function findByUserIdAndDateRange(string|int $userId, \DateTimeInterface $startDate, \DateTimeInterface $endDate): array
    {
        $userId = is_int($userId) ? (string) $userId : $userId;
        return $this->createQueryBuilder('w')
            ->andWhere('w.user = :userId')
            ->andWhere('w.date >= :startDate')
            ->andWhere('w.date <= :endDate')
            ->setParameter('userId', $userId)
            ->setParameter('startDate', $startDate)
            ->setParameter('endDate', $endDate)
            ->orderBy('w.date', 'ASC')
            ->getQuery()
            ->getResult();
    }

    /**
     * Get total water intake for a user on a specific date
     * @deprecated Use getTotalGlassesForUserUuidDate instead
     */
    public function getTotalGlassesForDate(string|int $userId, \DateTimeInterface $date): int
    {
        $userId = is_int($userId) ? (string) $userId : $userId;
        $result = $this->createQueryBuilder('w')
            ->select('SUM(w.glasses)')
            ->andWhere('w.user = :userId')
            ->andWhere('w.date = :date')
            ->setParameter('userId', $userId)
            ->setParameter('date', $date)
            ->getQuery()
            ->getSingleScalarResult();

        return $result ?? 0;
    }
}
