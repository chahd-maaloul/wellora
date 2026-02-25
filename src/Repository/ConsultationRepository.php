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
    public function findPendingOrderedByDate(?string $medecinUuid = null): array
    {
        $qb = $this->createQueryBuilder('c')
            ->leftJoin('c.patient', 'p')
            ->addSelect('p')
            ->leftJoin('c.medecin', 'm')
            ->where('c.status = :status')
            ->setParameter('status', 'pending');
        
        if ($medecinUuid !== null) {
            $qb->andWhere('m.uuid = :medecinUuid')
                ->setParameter('medecinUuid', $medecinUuid);
        }
        
        return $qb->orderBy('c.date_consultation', 'ASC')
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

    /**
     * Récupère les consultations acceptées pour un médecin spécifique
     * avec les informations patient
     */
    public function findAcceptedByMedecin(string $medecinUuid): array
    {
        return $this->createQueryBuilder('c')
            ->leftJoin('c.patient', 'p')
            ->addSelect('p')
            ->leftJoin('c.medecin', 'm')
            ->where('c.status = :status')
            ->setParameter('status', 'accepted')
            ->andWhere('m.uuid = :medecinUuid')
            ->setParameter('medecinUuid', $medecinUuid)
            ->orderBy('c.date_consultation', 'DESC')
            ->addOrderBy('c.time_consultation', 'DESC')
            ->getQuery()
            ->getResult();
    }

    /**
     * Compte les consultations en attente pour un médecin spécifique
     */
    public function countPendingByMedecin(string $medecinUuid): int
    {
        return $this->createQueryBuilder('c')
            ->select('COUNT(c.id)')
            ->leftJoin('c.medecin', 'm')
            ->where('c.status = :status')
            ->setParameter('status', 'pending')
            ->andWhere('m.uuid = :medecinUuid')
            ->setParameter('medecinUuid', $medecinUuid)
            ->getQuery()
            ->getSingleScalarResult();
    }

    public function findByMedecinOrderedByDateTime(string $medecinUuid, int $limit = 500): array
    {
        return $this->createQueryBuilder('c')
            ->leftJoin('c.patient', 'p')
            ->addSelect('p')
            ->leftJoin('c.medecin', 'm')
            ->where('m.uuid = :medecinUuid')
            ->setParameter('medecinUuid', $medecinUuid)
            ->orderBy('c.date_consultation', 'DESC')
            ->addOrderBy('c.time_consultation', 'DESC')
            ->setMaxResults($limit)
            ->getQuery()
            ->getResult();
    }

    public function findByMedecinAndPatientOrderedByDateTime(string $medecinUuid, string $patientUuid, ?\DateTimeInterface $since = null): array
    {
        $qb = $this->createQueryBuilder('c')
            ->leftJoin('c.patient', 'p')
            ->addSelect('p')
            ->leftJoin('c.medecin', 'm')
            ->where('m.uuid = :medecinUuid')
            ->andWhere('p.uuid = :patientUuid')
            ->setParameter('medecinUuid', $medecinUuid)
            ->setParameter('patientUuid', $patientUuid)
            ->orderBy('c.date_consultation', 'DESC')
            ->addOrderBy('c.time_consultation', 'DESC');

        if ($since) {
            $qb->andWhere('c.date_consultation >= :since')
                ->setParameter('since', $since);
        }

        return $qb->getQuery()->getResult();
    }

    public function findByMedecinSinceOrderedByDateTime(string $medecinUuid, \DateTimeInterface $since, int $limit = 2000): array
    {
        return $this->createQueryBuilder('c')
            ->leftJoin('c.patient', 'p')
            ->addSelect('p')
            ->leftJoin('c.medecin', 'm')
            ->where('m.uuid = :medecinUuid')
            ->andWhere('c.date_consultation >= :since')
            ->setParameter('medecinUuid', $medecinUuid)
            ->setParameter('since', $since)
            ->orderBy('c.date_consultation', 'DESC')
            ->addOrderBy('c.time_consultation', 'DESC')
            ->setMaxResults($limit)
            ->getQuery()
            ->getResult();
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    public function getDoctorRevenueSeries(string $medecinUuid, \DateTimeInterface $since): array
    {
        return $this->createQueryBuilder('c')
            ->select('c.date_consultation AS day')
            ->addSelect('SUM(c.fee) AS revenue')
            ->addSelect('COUNT(c.id) AS count')
            ->leftJoin('c.medecin', 'm')
            ->where('m.uuid = :medecinUuid')
            ->andWhere('c.date_consultation >= :since')
            ->setParameter('medecinUuid', $medecinUuid)
            ->setParameter('since', $since)
            ->groupBy('c.date_consultation')
            ->orderBy('c.date_consultation', 'ASC')
            ->getQuery()
            ->getArrayResult();
    }

    /**
     * Stats agrégées par médecin pour l'IA.
     *
     * @return array<int, array<string, mixed>>
     */
    public function getDoctorStats(\DateTimeInterface $since): array
    {
        return $this->createQueryBuilder('c')
            ->select('m.uuid AS doctor_uuid')
            ->addSelect('COUNT(c.id) AS total_count')
            ->addSelect('AVG(c.duration) AS avg_duration')
            ->addSelect('SUM(CASE WHEN c.consultation_type = :emergencyType OR c.status = :emergencyStatus THEN 1 ELSE 0 END) AS emergency_count')
            ->addSelect('SUM(CASE WHEN c.date_consultation >= :since THEN 1 ELSE 0 END) AS recent_count')
            ->leftJoin('c.medecin', 'm')
            ->where('m.uuid IS NOT NULL')
            ->groupBy('m.uuid')
            ->setParameter('emergencyType', 'emergency')
            ->setParameter('emergencyStatus', 'emergency')
            ->setParameter('since', $since)
            ->getQuery()
            ->getArrayResult();
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
