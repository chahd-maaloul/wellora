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
        string $dateSortOrder = 'DESC',
        ?string $hashtag = null
    ): array
    {
        $sortOrder = strtoupper($dateSortOrder);

        $qb = $this->createQueryBuilder('pp');

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

        if ($hashtag !== null && $hashtag !== '') {
            $qb
                ->andWhere('LOWER(pp.textPublication) LIKE :hashtagPattern')
                ->setParameter('hashtagPattern', '%#' . mb_strtolower($hashtag, 'UTF-8') . '%');
        }

        if ($sortOrder === 'HOT') {
            $qb
                ->leftJoin('pp.commentairePublications', 'cp')
                ->addSelect('(COUNT(cp.id) * 10 - DATE_DIFF(CURRENT_DATE(), pp.datePublication)) AS HIDDEN hotScore')
                ->groupBy('pp.id')
                ->orderBy('hotScore', 'DESC')
                ->addOrderBy('pp.datePublication', 'DESC')
                ->addOrderBy('pp.id', 'DESC');

            return $qb->getQuery()->getResult();
        }

        $direction = $sortOrder === 'ASC' ? 'ASC' : 'DESC';
        $qb
            ->orderBy('pp.datePublication', $direction)
            ->addOrderBy('pp.id', $direction);

        return $qb->getQuery()->getResult();
    }

    /**
     * @param int[] $publicationIds
     * @return array<int, array{hotScore: int, commentCount: int, ageDays: int}>
     */
    public function findHotMetricsForPublicationIds(array $publicationIds): array
    {
        $publicationIds = array_values(array_unique(array_map(
            static fn ($id): int => (int) $id,
            array_filter($publicationIds, static fn ($id): bool => (int) $id > 0)
        )));
        if ($publicationIds === []) {
            return [];
        }

        $rows = $this->createQueryBuilder('pp')
            ->select('pp.id AS publicationId')
            ->addSelect('COUNT(cp.id) AS commentCount')
            ->addSelect('DATE_DIFF(CURRENT_DATE(), pp.datePublication) AS ageDays')
            ->addSelect('(COUNT(cp.id) * 10 - DATE_DIFF(CURRENT_DATE(), pp.datePublication)) AS hotScore')
            ->leftJoin('pp.commentairePublications', 'cp')
            ->andWhere('pp.id IN (:publicationIds)')
            ->setParameter('publicationIds', $publicationIds)
            ->groupBy('pp.id')
            ->getQuery()
            ->getArrayResult();

        $metrics = [];
        foreach ($rows as $row) {
            $publicationId = (int) ($row['publicationId'] ?? 0);
            if ($publicationId <= 0) {
                continue;
            }

            $metrics[$publicationId] = [
                'hotScore' => (int) ($row['hotScore'] ?? 0),
                'commentCount' => (int) ($row['commentCount'] ?? 0),
                'ageDays' => max(0, (int) ($row['ageDays'] ?? 0)),
            ];
        }

        return $metrics;
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
