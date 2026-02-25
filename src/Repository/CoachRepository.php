<?php

namespace App\Repository;

use App\Entity\Coach;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class CoachRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Coach::class);
    }

    /**
     * Find coaches by specialty
     */
    public function findBySpecialty(string $specialty): array
    {
        return $this->createQueryBuilder('c')
            ->andWhere('c.specialite = :specialty')
            ->setParameter('specialty', $specialty)
            ->orderBy('c.yearsOfExperience', 'DESC')
            ->getQuery()
            ->getResult();
    }

    /**
     * Find verified coaches
     */
    public function findVerifiedCoaches(): array
    {
        return $this->createQueryBuilder('c')
            ->andWhere('c.isVerifiedByAdmin = :verified')
            ->setParameter('verified', true)
            ->orderBy('c.yearsOfExperience', 'DESC')
            ->getQuery()
            ->getResult();
    }

    /**
     * Find unverified coaches
     */
    public function findUnverifiedCoaches(): array
    {
        return $this->createQueryBuilder('c')
            ->andWhere('c.isVerifiedByAdmin = :verified')
            ->setParameter('verified', false)
            ->orderBy('c.createdAt', 'ASC')
            ->getQuery()
            ->getResult();
    }

    /**
     * Find coaches with minimum experience
     */
    public function findByMinExperience(int $years): array
    {
        return $this->createQueryBuilder('c')
            ->andWhere('c.yearsOfExperience >= :years')
            ->setParameter('years', $years)
            ->orderBy('c.yearsOfExperience', 'DESC')
            ->getQuery()
            ->getResult();
    }
   
}
