<?php

namespace App\Repository;

use App\Entity\ParcoursDeSante;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<ParcoursDeSante>
 */
class ParcoursDeSanteRepository extends ServiceEntityRepository
{
    private const DISTANCE_MIN_BOUND = 0.0;
    private const DISTANCE_MAX_BOUND = 20.0;
    private const PUBLICATION_MIN_BOUND = 0;
    private const PUBLICATION_MAX_BOUND = 200;

    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, ParcoursDeSante::class);
    }

    /**
     * @return array{minBound: float, maxBound: float, minValue: float, maxValue: float}
     */
    public function distanceRangeFilter(?float $initialMin, ?float $initialMax): array
    {
        $normalizedMin = $initialMin !== null
            ? max(self::DISTANCE_MIN_BOUND, min($initialMin, self::DISTANCE_MAX_BOUND))
            : self::DISTANCE_MIN_BOUND;
        $normalizedMax = $initialMax !== null
            ? max(self::DISTANCE_MIN_BOUND, min($initialMax, self::DISTANCE_MAX_BOUND))
            : self::DISTANCE_MAX_BOUND;

        return [
            'minBound' => self::DISTANCE_MIN_BOUND,
            'maxBound' => self::DISTANCE_MAX_BOUND,
            'minValue' => min($normalizedMin, $normalizedMax),
            'maxValue' => max($normalizedMin, $normalizedMax),
        ];
    }

    /**
     * @return array{minBound: int, maxBound: int, minValue: int, maxValue: int}
     */
    public function publicationRangeFilter(?int $initialMin, ?int $initialMax): array
    {
        $normalizedMin = $initialMin !== null
            ? max(self::PUBLICATION_MIN_BOUND, min($initialMin, self::PUBLICATION_MAX_BOUND))
            : self::PUBLICATION_MIN_BOUND;
        $normalizedMax = $initialMax !== null
            ? max(self::PUBLICATION_MIN_BOUND, min($initialMax, self::PUBLICATION_MAX_BOUND))
            : self::PUBLICATION_MAX_BOUND;

        return [
            'minBound' => self::PUBLICATION_MIN_BOUND,
            'maxBound' => self::PUBLICATION_MAX_BOUND,
            'minValue' => min($normalizedMin, $normalizedMax),
            'maxValue' => max($normalizedMin, $normalizedMax),
        ];
    }

    /**
     * @return array{viewMode: string, sortBy: string, filters: array{search: string, distance: array{0: float, 1: float}}}
     */
    public function parcoursDiscovery(): array
    {
        return [
            'viewMode' => 'grid',
            'sortBy' => 'recent',
            'filters' => [
                'search' => '',
                'distance' => [self::DISTANCE_MIN_BOUND, self::DISTANCE_MAX_BOUND],
            ],
        ];
    }

    /**
     * @return array{sortBy: string, sortOrder: string}
     */
    public function normalizeSort(string $sortBy, string $sortOrder): array
    {
        $allowedSortBy = ['date', 'name', 'distance'];
        $normalizedSortBy = in_array(strtolower($sortBy), $allowedSortBy, true) ? strtolower($sortBy) : 'date';
        $normalizedSortOrder = strtoupper($sortOrder) === 'ASC' ? 'ASC' : 'DESC';

        return [
            'sortBy' => $normalizedSortBy,
            'sortOrder' => $normalizedSortOrder,
        ];
    }

    /**
     * @return ParcoursDeSante[]
     */
    public function searchByNameAndLocation(
        ?string $nomParcours,
        ?string $localisationParcours,
        ?float $minDistance = null,
        ?float $maxDistance = null,
        ?int $minPublicationCount = null,
        ?int $maxPublicationCount = null,
        string $sortBy = 'date',
        string $sortOrder = 'DESC'
    ): array
    {
        $qb = $this->createQueryBuilder('p');

        if ($nomParcours !== null && trim($nomParcours) !== '') {
            $qb
                ->andWhere('LOWER(p.nomParcours) LIKE :nomParcours')
                ->setParameter('nomParcours', '%' . strtolower(trim($nomParcours)) . '%');
        }

        if ($localisationParcours !== null && trim($localisationParcours) !== '') {
            $qb
                ->andWhere('LOWER(p.localisationParcours) LIKE :localisationParcours')
                ->setParameter('localisationParcours', '%' . strtolower(trim($localisationParcours)) . '%');
        }

        if ($minDistance !== null) {
            $qb
                ->andWhere('p.distanceParcours >= :minDistance')
                ->setParameter('minDistance', $minDistance);
        }

        if ($maxDistance !== null) {
            $qb
                ->andWhere('p.distanceParcours <= :maxDistance')
                ->setParameter('maxDistance', $maxDistance);
        }

        if ($minPublicationCount !== null) {
            $minPublicationCountSubquery = '(SELECT COUNT(ppCountMin.id) FROM App\Entity\PublicationParcours ppCountMin WHERE ppCountMin.ParcoursDeSante = p)';
            $qb
                ->andWhere($minPublicationCountSubquery . ' >= :minPublicationCount')
                ->setParameter('minPublicationCount', $minPublicationCount);
        }

        if ($maxPublicationCount !== null) {
            $maxPublicationCountSubquery = '(SELECT COUNT(ppCountMax.id) FROM App\Entity\PublicationParcours ppCountMax WHERE ppCountMax.ParcoursDeSante = p)';
            $qb
                ->andWhere($maxPublicationCountSubquery . ' <= :maxPublicationCount')
                ->setParameter('maxPublicationCount', $maxPublicationCount);
        }

        $allowedSortFields = [
            'date' => 'p.dateCreation',
            'name' => 'p.nomParcours',
            'distance' => 'p.distanceParcours',
        ];

        $sortField = $allowedSortFields[$sortBy] ?? $allowedSortFields['date'];
        $direction = strtoupper($sortOrder) === 'ASC' ? 'ASC' : 'DESC';

        $qb->orderBy($sortField, $direction);

        // Stable secondary sort for deterministic order.
        if ($sortField !== 'p.nomParcours') {
            $qb->addOrderBy('p.nomParcours', 'ASC');
        }

        return $qb->getQuery()->getResult();
    }

    //    /**
    //     * @return ParcoursDeSante[] Returns an array of ParcoursDeSante objects
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

    //    public function findOneBySomeField($value): ?ParcoursDeSante
    //    {
    //        return $this->createQueryBuilder('p')
    //            ->andWhere('p.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->getQuery()
    //            ->getOneOrNullResult()
    //        ;
    //    }
}
