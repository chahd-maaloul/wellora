<?php

namespace App\Repository;

use App\Entity\ParcoursDeSante;
use App\Entity\PublicationParcours;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<PublicationParcours>
 */
class PublicationParcoursRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, PublicationParcours::class);
    }

    /**
     * @return PublicationParcours[]
     */
    public function findByFilters(
        ?ParcoursDeSante $parcoursDeSante = null,
        ?string $experience = null,
        ?string $typePublication = null,
        string $dateSortOrder = 'DESC'
    ): array
    {
        $direction = strtoupper($dateSortOrder) === 'ASC' ? 'ASC' : 'DESC';

        $qb = $this->createQueryBuilder('pp')
            ->orderBy('pp.datePublication', $direction)
            ->addOrderBy('pp.id', $direction);

        if ($parcoursDeSante !== null) {
            $qb
                ->andWhere('pp.ParcoursDeSante = :parcours')
                ->setParameter('parcours', $parcoursDeSante);
        }

        if ($experience !== null && $experience !== '') {
            $qb
                ->andWhere('pp.experience = :experience')
                ->setParameter('experience', $experience);
        }

        if ($typePublication !== null && $typePublication !== '') {
            $qb
                ->andWhere('pp.typePublication = :typePublication')
                ->setParameter('typePublication', $typePublication);
        }

        return $qb->getQuery()->getResult();
    }

    //    /**
    //     * @return PublicationParcours[] Returns an array of PublicationParcours objects
    //     */
    //    public function findByExampleField($value): array
    //    {
    //        return $this->createQueryBuilder('p')
    //            ->andWhere('p.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->orderBy('p.id', 'ASC')
    //            ->setMaxResults(10)
    //            ->getQuery()
    //            ->getResult()
    //        ;
    //    }

    //    public function findOneBySomeField($value): ?PublicationParcours
    //    {
    //        return $this->createQueryBuilder('p')
    //            ->andWhere('p.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->getQuery()
    //            ->getOneOrNullResult()
    //        ;
    //    }
}
