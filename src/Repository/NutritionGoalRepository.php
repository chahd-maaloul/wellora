<?php

namespace App\Repository;

use App\Entity\NutritionGoal;
use App\Entity\User;
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
     * Find nutrition goal by user UUID
     */
    public function findByUserUuid(string $userUuid): ?NutritionGoal
    {
        return $this->findOneBy(['user' => $userUuid]);
    }

    /**
     * Find nutrition goal by User entity
     */
    public function findByUser(User $user): ?NutritionGoal
    {
        return $this->findOneBy(['user' => $user]);
    }

    /**
     * Find all nutrition goals for a user UUID
     */
    public function findAllByUserUuid(string $userUuid): array
    {
        return $this->findBy(['user' => $userUuid]);
    }

    /**
     * Find all nutrition goals for a User entity
     */
    public function findAllByUser(User $user): array
    {
        return $this->findBy(['user' => $user]);
    }

    /**
     * Find nutrition goal by user ID (legacy method - now uses UUID)
     */
    public function findByUserId(string|int $userId): ?NutritionGoal
    {
        // Convert to string if it's an integer (for backward compatibility)
        $userId = is_int($userId) ? (string) $userId : $userId;
        return $this->findOneBy(['user' => $userId]);
    }

    /**
     * Find all nutrition goals for a user ID (legacy method - now uses UUID)
     */
    public function findAllByUserId(string|int $userId): array
    {
        // Convert to string if it's an integer (for backward compatibility)
        $userId = is_int($userId) ? (string) $userId : $userId;
        return $this->findBy(['user' => $userId]);
    }
}
