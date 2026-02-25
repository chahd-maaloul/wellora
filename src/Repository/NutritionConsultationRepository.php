<?php

namespace App\Repository;

use App\Entity\NutritionConsultation;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<NutritionConsultation>
 */
class NutritionConsultationRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, NutritionConsultation::class);
    }

    /**
     * Find all consultations for a patient
     */
    public function findByPatient(int $patientId): array
    {
        return $this->createQueryBuilder('c')
            ->andWhere('c.patientId = :patientId')
            ->setParameter('patientId', $patientId)
            ->orderBy('c.scheduledAt', 'DESC')
            ->getQuery()
            ->getResult();
    }

    /**
     * Find all consultations for a nutritionist
     */
    public function findByNutritionist(int $nutritionistId): array
    {
        return $this->createQueryBuilder('c')
            ->andWhere('c.nutritionistId = :nutritionistId')
            ->setParameter('nutritionistId', $nutritionistId)
            ->orderBy('c.scheduledAt', 'DESC')
            ->getQuery()
            ->getResult();
    }

    /**
     * Find upcoming consultations for a nutritionist
     */
    public function findUpcomingByNutritionist(int $nutritionistId): array
    {
        return $this->createQueryBuilder('c')
            ->andWhere('c.nutritionistId = :nutritionistId')
            ->andWhere('c.scheduledAt >= :now')
            ->andWhere('c.status != :status')
            ->setParameter('nutritionistId', $nutritionistId)
            ->setParameter('now', new \DateTime())
            ->setParameter('status', 'cancelled')
            ->orderBy('c.scheduledAt', 'ASC')
            ->getQuery()
            ->getResult();
    }

    /**
     * Find upcoming consultations for a patient
     */
    public function findUpcomingByPatient(int $patientId): array
    {
        return $this->createQueryBuilder('c')
            ->andWhere('c.patientId = :patientId')
            ->andWhere('c.scheduledAt >= :now')
            ->andWhere('c.status != :status')
            ->setParameter('patientId', $patientId)
            ->setParameter('now', new \DateTime())
            ->setParameter('status', 'cancelled')
            ->orderBy('c.scheduledAt', 'ASC')
            ->getQuery()
            ->getResult();
    }

    /**
     * Find past consultations
     */
    public function findPastByPatient(int $patientId): array
    {
        return $this->createQueryBuilder('c')
            ->andWhere('c.patientId = :patientId')
            ->andWhere('c.scheduledAt < :now')
            ->setParameter('patientId', $patientId)
            ->setParameter('now', new \DateTime())
            ->orderBy('c.scheduledAt', 'DESC')
            ->getQuery()
            ->getResult();
    }

    /**
     * Find today's consultations for a nutritionist
     */
    public function findTodayByNutritionist(int $nutritionistId): array
    {
        $today = new \DateTime();
        $today->setTime(0, 0, 0);
        $tomorrow = clone $today;
        $tomorrow->modify('+1 day');

        return $this->createQueryBuilder('c')
            ->andWhere('c.nutritionistId = :nutritionistId')
            ->andWhere('c.scheduledAt >= :today')
            ->andWhere('c.scheduledAt < :tomorrow')
            ->setParameter('nutritionistId', $nutritionistId)
            ->setParameter('today', $today)
            ->setParameter('tomorrow', $tomorrow)
            ->orderBy('c.scheduledAt', 'ASC')
            ->getQuery()
            ->getResult();
    }

    /**
     * Count total consultations for a patient
     */
    public function countByPatient(int $patientId): int
    {
        return $this->createQueryBuilder('c')
            ->select('COUNT(c.id)')
            ->andWhere('c.patientId = :patientId')
            ->setParameter('patientId', $patientId)
            ->getQuery()
            ->getSingleScalarResult();
    }

    /**
     * Count completed consultations for a patient
     */
    public function countCompletedByPatient(int $patientId): int
    {
        return $this->createQueryBuilder('c')
            ->select('COUNT(c.id)')
            ->andWhere('c.patientId = :patientId')
            ->andWhere('c.status = :status')
            ->setParameter('patientId', $patientId)
            ->setParameter('status', 'completed')
            ->getQuery()
            ->getSingleScalarResult();
    }

    /**
     * Find consultation by ID
     */
    public function findById(int $id): ?NutritionConsultation
    {
        return $this->createQueryBuilder('c')
            ->andWhere('c.id = :id')
            ->setParameter('id', $id)
            ->getQuery()
            ->getOneOrNullResult();
    }

    /**
     * Find pending consultations for a nutritionist
     */
    public function findPendingByNutritionist(int $nutritionistId): array
    {
        return $this->createQueryBuilder('c')
            ->andWhere('c.nutritionistId = :nutritionistId')
            ->andWhere('c.status = :status')
            ->setParameter('nutritionistId', $nutritionistId)
            ->setParameter('status', 'pending')
            ->orderBy('c.scheduledAt', 'ASC')
            ->getQuery()
            ->getResult();
    }
}
