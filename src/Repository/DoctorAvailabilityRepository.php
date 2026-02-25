<?php

namespace App\Repository;

use App\Entity\DoctorAvailability;
use App\Entity\Medecin;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<DoctorAvailability>
 */
class DoctorAvailabilityRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, DoctorAvailability::class);
    }

    /**
     * @return DoctorAvailability[]
     */
    public function findByMedecin(Medecin $medecin): array
    {
        return $this->createQueryBuilder('a')
            ->where('a.medecin = :medecin')
            ->setParameter('medecin', $medecin)
            ->orderBy('FIELD(a.dayOfWeek, \'monday\', \'tuesday\', \'wednesday\', \'thursday\', \'friday\', \'saturday\', \'sunday\')', 'ASC')
            ->getQuery()
            ->getResult();
    }

    public function findByMedecinAndDay(Medecin $medecin, string $dayOfWeek): ?DoctorAvailability
    {
        return $this->createQueryBuilder('a')
            ->where('a.medecin = :medecin')
            ->andWhere('a.dayOfWeek = :day')
            ->setParameter('medecin', $medecin)
            ->setParameter('day', $dayOfWeek)
            ->getQuery()
            ->getOneOrNullResult();
    }

    public function save(DoctorAvailability $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);
        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(DoctorAvailability $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);
        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }
}
