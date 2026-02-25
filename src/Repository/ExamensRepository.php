<?php

namespace App\Repository;

use App\Entity\Examens;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Examens>
 */
class ExamensRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Examens::class);
    }

    /**
     * @return Examens[]
     */
    public function findByPatientUuid(string $patientUuid): array
    {
        return $this->createQueryBuilder('e')
            ->leftJoin('e.consultation', 'c')
            ->leftJoin('c.patient', 'p')
            ->andWhere('p.uuid = :uuid')
            ->setParameter('uuid', $patientUuid)
            ->orderBy('e.date_examen', 'DESC')
            ->getQuery()
            ->getResult();
    }

    public function findOneForPatient(int $examId, string $patientUuid): ?Examens
    {
        return $this->createQueryBuilder('e')
            ->leftJoin('e.consultation', 'c')
            ->leftJoin('c.patient', 'p')
            ->andWhere('e.id = :id')
            ->andWhere('p.uuid = :uuid')
            ->setParameter('id', $examId)
            ->setParameter('uuid', $patientUuid)
            ->getQuery()
            ->getOneOrNullResult();
    }

    public function findOneForDoctor(int $examId, string $doctorUuid): ?Examens
    {
        return $this->createQueryBuilder('e')
            ->leftJoin('e.consultation', 'c')
            ->leftJoin('c.medecin', 'm')
            ->leftJoin('e.prescribedBy', 'p')
            ->andWhere('e.id = :id')
            ->andWhere('(m.uuid = :uuid OR p.uuid = :uuid)')
            ->setParameter('id', $examId)
            ->setParameter('uuid', $doctorUuid)
            ->getQuery()
            ->getOneOrNullResult();
    }

    //    /**
    //     * @return Examens[] Returns an array of Examens objects
    //     */
    //    public function findByExampleField($value): array
    //    {
    //        return $this->createQueryBuilder('e')
    //            ->andWhere('e.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->orderBy('e.id', 'ASC')
    //            ->setMaxResults(10)
    //            ->getQuery()
    //            ->getResult()
    //        ;
    //    }

    //    public function findOneBySomeField($value): ?Examens
    //    {
    //        return $this->createQueryBuilder('e')
    //            ->andWhere('e.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->getQuery()
    //            ->getOneOrNullResult()
    //        ;
    //    }
}
