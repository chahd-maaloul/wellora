<?php

namespace App\Repository;

use App\Entity\Symptom;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Symptom>
 *
 * @method Symptom|null find($id, $lockMode = null, $lockVersion = null)
 * @method Symptom|null findOneBy(array $criteria, array $orderBy = null)
 * @method Symptom[] findAll()
 * @method Symptom[] findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class SymptomRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Symptom::class);
    }
}
