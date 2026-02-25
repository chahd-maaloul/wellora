<?php

namespace App\Repository;

use App\Entity\DoctorSubstitution;
use App\Entity\Medecin;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<DoctorSubstitution>
 */
class DoctorSubstitutionRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, DoctorSubstitution::class);
    }

    /**
     * @return DoctorSubstitution[]
     */
    public function findByMedecin(Medecin $medecin): array
    {
        return $this->createQueryBuilder('s')
            ->where('s.medecin = :medecin')
            ->setParameter('medecin', $medecin)
            ->orderBy('s.startDate', 'DESC')
            ->getQuery()
            ->getResult();
    }

    /**
     * @return DoctorSubstitution[]
     */
    public function findActiveSubstitutionsForMedecin(Medecin $medecin): array
    {
        return $this->createQueryBuilder('s')
            ->where('s.medecin = :medecin')
            ->andWhere('s.endDate >= :today')
            ->andWhere('s.status = :accepted')
            ->setParameter('medecin', $medecin)
            ->setParameter('today', new \DateTime())
            ->setParameter('accepted', DoctorSubstitution::STATUS_ACCEPTED)
            ->orderBy('s.startDate', 'ASC')
            ->getQuery()
            ->getResult();
    }

    /**
     * @return DoctorSubstitution[]
     */
    public function findPendingRequestsForSubstitute(Medecin $substitute): array
    {
        return $this->createQueryBuilder('s')
            ->where('s.substitute = :substitute')
            ->andWhere('s.status = :pending')
            ->setParameter('substitute', $substitute)
            ->setParameter('pending', DoctorSubstitution::STATUS_PENDING)
            ->orderBy('s.startDate', 'ASC')
            ->getQuery()
            ->getResult();
    }

    /**
     * Get all available doctors for substitution (excluding the current doctor)
     * @return Medecin[]
     */
    public function findAvailableSubstitutes(Medecin $excludeMedecin): array
    {
        return $this->getEntityManager()
            ->getRepository(Medecin::class)
            ->createQueryBuilder('m')
            ->where('m != :exclude')
            ->andWhere('m.isActive = true')
            ->setParameter('exclude', $excludeMedecin)
            ->orderBy('m.firstName', 'ASC')
            ->addOrderBy('m.lastName', 'ASC')
            ->getQuery()
            ->getResult();
    }

    public function save(DoctorSubstitution $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);
        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(DoctorSubstitution $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);
        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }
}
