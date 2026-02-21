<?php

namespace App\Repository;

use App\Entity\WaterIntake;
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
     * Find water intake by user ID and date
     */
    public function findByUserIdAndDate(int $userId, \DateTimeInterface $date): ?WaterIntake
    {
        return $this->findOneBy([
            'userId' => $userId,
            'date' => $date
        ]);
    }

    /**
     * Find all water intakes for a user on a specific date
     */
    public function findAllByUserIdAndDate(int $userId, \DateTimeInterface $date): array
    {
        return $this->findBy([
            'userId' => $userId,
            'date' => $date
        ]);
    }

    /**
     * Find all water intakes for a user
     */
    public function findAllByUserId(int $userId): array
    {
        return $this->findBy(['userId' => $userId]);
    }

    /**
     * Find water intakes by user ID and date range
     */
    public function findByUserIdAndDateRange(int $userId, \DateTimeInterface $startDate, \DateTimeInterface $endDate): array
    {
        return $this->createQueryBuilder('w')
            ->andWhere('w.userId = :userId')
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
     */
    public function getTotalGlassesForDate(int $userId, \DateTimeInterface $date): int
    {
        $result = $this->createQueryBuilder('w')
            ->select('SUM(w.glasses)')
            ->andWhere('w.userId = :userId')
            ->andWhere('w.date = :date')
            ->setParameter('userId', $userId)
            ->setParameter('date', $date)
            ->getQuery()
            ->getSingleScalarResult();

        return $result ?? 0;
    }
}
