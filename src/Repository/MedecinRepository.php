<?php

namespace App\Repository;

use App\Entity\Medecin;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class MedecinRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Medecin::class);
    }

    public function findVerifiedMedecins(): array
    {
        return $this->createQueryBuilder('m')
            ->andWhere('m.isVerifiedByAdmin = true')
            ->andWhere('m.isActive = true')
            ->orderBy('m.specialite', 'ASC')
            ->getQuery()
            ->getResult();
    }

    public function findBySpeciality(string $speciality): array
    {
        return $this->createQueryBuilder('m')
            ->andWhere('m.specialite = :speciality')
            ->setParameter('speciality', $speciality)
            ->andWhere('m.isVerifiedByAdmin = true')
            ->andWhere('m.isActive = true')
            ->getQuery()
            ->getResult();
    }

    public function findUnverifiedMedecins(): array
    {
        return $this->createQueryBuilder('m')
            ->andWhere('m.isVerifiedByAdmin = false')
            ->orderBy('m.createdAt', 'ASC')
            ->getQuery()
            ->getResult();
    }
}
