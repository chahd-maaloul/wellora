<?php

namespace App\Repository;

use App\Entity\NutritionGoal;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<NutritionGoal>
 */
class NutritionGoalRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, NutritionGoal::class);
    }

    /**
     * Find nutrition goal by user ID
     */
    public function findByUserId(int $userId): ?NutritionGoal
    {
        return $this->findOneBy(['userId' => $userId]);
    }

    /**
     * Find all nutrition goals for a user
     */
    public function findAllByUserId(int $userId): array
    {
        return $this->findBy(['userId' => $userId]);
    }
}
