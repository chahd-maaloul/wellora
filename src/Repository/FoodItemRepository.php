<?php

namespace App\Repository;

use App\Entity\FoodItem;
use App\Entity\Patient;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Doctrine\ORM\QueryBuilder;

/**
 * @extends ServiceEntityRepository<FoodItem>
 */
class FoodItemRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, FoodItem::class);
    }

    /**
     * Find food items for a patient within a date range
     */
    public function findByPatientAndDateRange(Patient $patient, \DateTime $startDate, \DateTime $endDate): array
    {
        return $this->createQueryBuilder('f')
            ->andWhere('f.patient = :patient')
            ->andWhere('f.loggedAt >= :startDate')
            ->andWhere('f.loggedAt <= :endDate')
            ->setParameter('patient', $patient)
            ->setParameter('startDate', $startDate)
            ->setParameter('endDate', $endDate)
            ->orderBy('f.loggedAt', 'DESC')
            ->getQuery()
            ->getResult();
    }

    /**
     * Get distinct dates when a patient logged food items within a date range
     */
    public function getLoggedDatesByPatient(Patient $patient, \DateTime $startDate, \DateTime $endDate): array
    {
        $qb = $this->createQueryBuilder('f');
        $qb->select('DISTINCT DATE(f.loggedAt) as loggedDate')
            ->andWhere('f.patient = :patient')
            ->andWhere('f.loggedAt >= :startDate')
            ->andWhere('f.loggedAt <= :endDate')
            ->setParameter('patient', $patient)
            ->orderBy('loggedDate', 'DESC');

        $result = $qb->getQuery()->getScalarResult();
        return array_column($result, 'loggedDate');
    }

    /**
     * Check if patient has logged food on a specific date
     */
    public function hasLoggedOnDate(Patient $patient, \DateTime $date): bool
    {
        $startOfDay = clone $date;
        $startOfDay->setTime(0, 0, 0);
        $endOfDay = clone $date;
        $endOfDay->setTime(23, 59, 59);

        $count = $this->createQueryBuilder('f')
            ->select('COUNT(f.id)')
            ->andWhere('f.patient = :patient')
            ->andWhere('f.loggedAt >= :startDate')
            ->andWhere('f.loggedAt <= :endDate')
            ->setParameter('patient', $patient)
            ->setParameter('startDate', $startOfDay)
            ->setParameter('endDate', $endOfDay)
            ->getQuery()
            ->getSingleScalarResult();

        return $count > 0;
    }
}
