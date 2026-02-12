<?php

namespace App\Repository;

use App\Entity\Consultation;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Doctrine\ORM\QueryBuilder;

/**
 * @extends ServiceEntityRepository<Consultation>
 */
class ConsultationRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Consultation::class);
    }

    public function searchForPatientList(
        ?string $search,
        ?string $status,
        ?string $condition,
        ?string $dateFrom,
        ?string $dateTo,
        ?string $sortBy,
        ?string $sortDir,
        int $page,
        int $limit
    ): array {
        $qb = $this->createQueryBuilder('c');

        $search = is_string($search) ? trim($search) : '';
        $status = is_string($status) ? trim($status) : '';
        $condition = is_string($condition) ? trim($condition) : '';
        $dateFrom = is_string($dateFrom) ? trim($dateFrom) : '';
        $dateTo = is_string($dateTo) ? trim($dateTo) : '';
        $sortBy = is_string($sortBy) ? trim($sortBy) : '';
        $sortDir = strtoupper(is_string($sortDir) ? trim($sortDir) : 'DESC');
        $sortDir = $sortDir === 'ASC' ? 'ASC' : 'DESC';

        if ($search !== '') {
            $qb->andWhere('c.reason_for_visit LIKE :search OR c.symptoms_description LIKE :search OR c.assessment LIKE :search')
                ->setParameter('search', '%' . $search . '%');
        }

        if ($status !== '' && $status !== 'all') {
            $qb->andWhere('c.status = :status')
                ->setParameter('status', $status);
        }

        if ($condition !== '' && $condition !== 'all') {
            $diagnosisValues = array_filter(array_map('trim', explode(',', $condition)));
            if (count($diagnosisValues) === 0) {
                $diagnosisValues = [$condition];
            }

            $orX = $qb->expr()->orX();
            foreach ($diagnosisValues as $idx => $diag) {
                if ($diag === '') {
                    continue;
                }
                $param = ':diagnosis_' . $idx;
                $orX->add("JSON_CONTAINS(c.diagnoses, {$param}) = 1");
                $qb->setParameter($param, json_encode($diag));
            }
            if ($orX->count() > 0) {
                $qb->andWhere($orX);
            }
        }

        $this->applyDateFilter($qb, $dateFrom, $dateTo);

        $this->applySort($qb, $sortBy, $sortDir);

        $page = max(1, $page);
        $limit = max(1, $limit);
        $qb->setFirstResult(($page - 1) * $limit)
            ->setMaxResults($limit);

        return $qb->getQuery()->getResult();
    }

    private function applySort(QueryBuilder $qb, string $sortBy, string $sortDir): void
    {
        switch ($sortBy) {
            case 'lastVisit':
                $qb->orderBy('c.date_consultation', $sortDir);
                break;
            case 'reason':
                $qb->orderBy('c.reason_for_visit', $sortDir);
                break;
            case 'status':
                $qb->orderBy('c.status', $sortDir);
                break;
            default:
                $qb->orderBy('c.date_consultation', 'DESC');
                break;
        }
    }

    private function applyDateFilter(QueryBuilder $qb, string $dateFrom, string $dateTo): void
    {
        if ($dateFrom !== '') {
            $from = $this->parseDate($dateFrom);
            if ($from) {
                $qb->andWhere('c.date_consultation >= :dateFrom')
                    ->setParameter('dateFrom', $from);
            }
        }

        if ($dateTo !== '') {
            $to = $this->parseDate($dateTo);
            if ($to) {
                $to->setTime(23, 59, 59);
                $qb->andWhere('c.date_consultation <= :dateTo')
                    ->setParameter('dateTo', $to);
            }
        }
    }

    private function parseDate(string $value): ?\DateTimeInterface
    {
        try {
            return new \DateTime($value);
        } catch (\Throwable $e) {
            return null;
        }
    }
 public function findPendingOrderedByDate(): array
    {
        return $this->createQueryBuilder('c')
            ->where('c.status = :status')
            ->setParameter('status', 'pending')
            ->orderBy('c.date_consultation', 'ASC')  // Notez le underscore !
            ->addOrderBy('c.time_consultation', 'ASC')
            ->getQuery()
            ->getResult();
    }

    /**
     * Récupère les rendez-vous acceptés triés par date et heure
     * EXCLUT les téléconsultations (phone, video, telemedicine)
     */
    public function findAcceptedOrderedByDate(): array
    {
        return $this->createQueryBuilder('c')
            ->where('c.status = :status')
            ->setParameter('status', 'accepted')
            ->andWhere('c.appointment_mode NOT IN (:modes)')
            ->setParameter('modes', ['phone', 'video', 'telemedicine'])
            ->orderBy('c.date_consultation', 'ASC')
            ->addOrderBy('c.time_consultation', 'ASC')
            ->getQuery()
            ->getResult();
    }

    /**
     * Récupère les rendez-vous par statut avec tri
     */
    public function findByStatusOrderedByDate(string $status): array
    {
        return $this->createQueryBuilder('c')
            ->where('c.status = :status')
            ->setParameter('status', $status)
            ->orderBy('c.date_consultation', 'ASC')
            ->addOrderBy('c.time_consultation', 'ASC')
            ->getQuery()
            ->getResult();
    }

//    /**
//     * @return Consultation[] Returns an array of Consultation objects
//     */
//    public function findByExampleField($value): array
//    {
//        return $this->createQueryBuilder('c')
//            ->andWhere('c.exampleField = :val')
//            ->setParameter('val', $value)
//            ->orderBy('c.id', 'ASC')
//            ->setMaxResults(10)
//            ->getQuery()
//            ->getResult()
//        ;
//    }

//    public function findOneBySomeField($value): ?Consultation
//    {
//        return $this->createQueryBuilder('c')
//            ->andWhere('c.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }
}
