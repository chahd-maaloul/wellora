<?php

namespace App\Repository;

use App\Entity\Healthentry;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Healthentry>
 *
 * @method Healthentry|null find($id, $lockMode = null, $lockVersion = null)
 * @method Healthentry|null findOneBy(array $criteria, array $orderBy = null)
 * @method Healthentry[] findAll()
 * @method Healthentry[] findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 * @method \Doctrine\ORM\QueryBuilder createQueryBuilder(string $alias)
 */
class HealthentryRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Healthentry::class);
    }

    /**
     * @return Healthentry[] Returns an array of Healthentry objects
     */
    public function findByJournalAndDateRange($journal, \DateTime $startDate, \DateTime $endDate): array
    {
        return $this->createQueryBuilder('h')
            ->andWhere('h.journal = :journal')
            ->setParameter('journal', $journal)
            ->andWhere('h.date >= :startDate')
            ->setParameter('startDate', $startDate)
            ->andWhere('h.date <= :endDate')
            ->setParameter('endDate', $endDate)
            ->orderBy('h.date', 'ASC')
            ->getQuery()
            ->getResult()
        ;
    }

    /**
     * @return Healthentry[] Returns an array of Healthentry objects filtered by month
     */
    public function findByMonth(int $year, int $month): array
    {
        $startDate = new \DateTime("$year-$month-01");
        $endDate = new \DateTime("$year-$month-01");
        $endDate->modify('last day of this month');
        
        return $this->createQueryBuilder('h')
            ->andWhere('h.date >= :startDate')
            ->setParameter('startDate', $startDate)
            ->andWhere('h.date <= :endDate')
            ->setParameter('endDate', $endDate)
            ->orderBy('h.date', 'ASC')
            ->getQuery()
            ->getResult()
        ;
    }

    //    public function findOneBySomeField($value): ?Healthentry
    //    {
    //        return $this->createQueryBuilder('h')
    //            ->andWhere('h.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->getQuery()
    //            ->getOneOrNullResult()
    //        ;
    //    }
}
