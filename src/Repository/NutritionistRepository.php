<?php

namespace App\Repository;

use App\Entity\Nutritionist;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class NutritionistRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Nutritionist::class);
    }

    /**
     * Find verified nutritionists
     */
    public function findVerifiedNutritionists(): array
    {
        return $this->createQueryBuilder('n')
            ->andWhere('n.isVerifiedByAdmin = :verified')
            ->setParameter('verified', true)
            ->orderBy('n.yearsOfExperience', 'DESC')
            ->getQuery()
            ->getResult();
    }

    /**
     * Find unverified nutritionists
     */
    public function findUnverifiedNutritionists(): array
    {
        return $this->createQueryBuilder('n')
            ->andWhere('n.isVerifiedByAdmin = :verified')
            ->setParameter('verified', false)
            ->orderBy('n.createdAt', 'ASC')
            ->getQuery()
            ->getResult();
    }

    /**
     * Find nutritionists with minimum experience
     */
    public function findByMinExperience(int $years): array
    {
        return $this->createQueryBuilder('n')
            ->andWhere('n.yearsOfExperience >= :years')
            ->setParameter('years', $years)
            ->orderBy('n.yearsOfExperience', 'DESC')
            ->getQuery()
            ->getResult();
    }

    /**
     * Find nutritionists by diploma URL presence
     */
    public function findWithDiploma(): array
    {
        return $this->createQueryBuilder('n')
            ->andWhere('n.diplomaUrl IS NOT NULL')
            ->andWhere('n.diplomaUrl != :empty')
            ->setParameter('empty', '')
            ->orderBy('n.yearsOfExperience', 'DESC')
            ->getQuery()
            ->getResult();
    }
}
