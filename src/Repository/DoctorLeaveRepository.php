<?php

namespace App\Repository;

use App\Entity\DoctorLeave;
use App\Entity\Medecin;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<DoctorLeave>
 */
class DoctorLeaveRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, DoctorLeave::class);
    }

    /**
     * @return DoctorLeave[]
     */
    public function findByMedecin(Medecin $medecin): array
    {
        return $this->createQueryBuilder('l')
            ->where('l.medecin = :medecin')
            ->setParameter('medecin', $medecin)
            ->orderBy('l.startDate', 'DESC')
            ->getQuery()
            ->getResult();
    }

    /**
     * @return DoctorLeave[]
     */
    public function findUpcomingByMedecin(Medecin $medecin): array
    {
        return $this->createQueryBuilder('l')
            ->where('l.medecin = :medecin')
            ->andWhere('l.endDate >= :today')
            ->andWhere('l.status != :cancelled')
            ->setParameter('medecin', $medecin)
            ->setParameter('today', new \DateTime())
            ->setParameter('cancelled', DoctorLeave::STATUS_CANCELLED)
            ->orderBy('l.startDate', 'ASC')
            ->getQuery()
            ->getResult();
    }

    public function save(DoctorLeave $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);
        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(DoctorLeave $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);
        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }
}
