<?php

namespace App\Repository;

use App\Entity\FoodPlan;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<FoodPlan>
 */
class FoodPlanRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, FoodPlan::class);
    }

    /**
     * Find food plans within a date range for a specific patient
     */
    public function findByDateRange($patient, \DateTime $startDate, \DateTime $endDate): array
    {
        return $this->createQueryBuilder('fp')
            ->join('fp.nutritionGoal', 'ng')
            ->where('ng.patient = :patient')
            ->andWhere('fp.planDate >= :startDate')
            ->andWhere('fp.planDate <= :endDate')
            ->setParameter('patient', $patient)
            ->setParameter('startDate', $startDate)
            ->setParameter('endDate', $endDate)
            ->getQuery()
            ->getResult();
    }
}
