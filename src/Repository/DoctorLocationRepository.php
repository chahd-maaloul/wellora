<?php

namespace App\Repository;

use App\Entity\DoctorLocation;
use App\Entity\Medecin;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<DoctorLocation>
 */
class DoctorLocationRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, DoctorLocation::class);
    }

    /**
     * @return DoctorLocation[]
     */
    public function findByMedecin(Medecin $medecin): array
    {
        return $this->createQueryBuilder('l')
            ->where('l.medecin = :medecin')
            ->setParameter('medecin', $medecin)
            ->orderBy('l.id', 'ASC')
            ->getQuery()
            ->getResult();
    }

    /**
     * Find by medecin UUID
     * @return DoctorLocation[]
     */
    public function findByMedecinUuid(string $uuid): array
    {
        return $this->createQueryBuilder('l')
            ->innerJoin('l.medecin', 'm')
            ->where('m.uuid = :uuid')
            ->setParameter('uuid', $uuid)
            ->orderBy('l.id', 'ASC')
            ->getQuery()
            ->getResult();
    }

    public function save(DoctorLocation $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);
        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(DoctorLocation $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);
        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }
}
