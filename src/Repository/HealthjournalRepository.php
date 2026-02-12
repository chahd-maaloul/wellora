<?php

namespace App\Repository;

use App\Entity\Healthjournal;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Healthjournal>
 *
 * @method Healthjournal|null find($id, $lockMode = null, $lockVersion = null)
 * @method Healthjournal|null findOneBy(array $criteria, array $orderBy = null)
 * @method Healthjournal[] findAll()
 * @method Healthjournal[] findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class HealthjournalRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Healthjournal::class);
    }

    //    /**
    //     * @return Healthjournal[] Returns an array of Healthjournal objects
    //     */
    //    public function findByExampleField($value): array
    //    {
    //        return $this->createQueryBuilder('h')
    //            ->andWhere('h.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->orderBy('h.id', 'ASC')
    //            ->setMaxResults(10)
    //            ->getQuery()
    //            ->getResult()
    //        ;
    //    }

    //    public function findOneBySomeField($value): ?Healthjournal
    //    {
    //        return $this->createQueryBuilder('h')
    //            ->andWhere('h.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->getQuery()
    //            ->getOneOrNullResult()
    //        ;
    //    }
}
