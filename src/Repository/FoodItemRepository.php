<?php

namespace App\Repository;

use App\Entity\FoodItem;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<FoodItem>
 */
class FoodItemRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, FoodItem::class);
    }

    /**
     * Find food items by food log ID
     */
    public function findByFoodLogId(int $foodLogId): array
    {
        return $this->findBy(['foodLog' => $foodLogId]);
    }

    /**
     * Find frequently logged foods by user ID
     */
    public function findFrequentlyLoggedFoods(int $userId, int $limit = 10): array
    {
        return $this->createQueryBuilder('f')
            ->select('f.name, COUNT(f.id) as count, AVG(f.calories) as avgCalories')
            ->join('f.foodLog', 'fl')
            ->andWhere('fl.userId = :userId')
            ->groupBy('f.name')
            ->orderBy('count', 'DESC')
            ->setMaxResults($limit)
            ->getQuery()
            ->getResult();
    }

    /**
     * Search foods by name
     */
    public function searchByName(string $name, int $limit = 20): array
    {
        return $this->createQueryBuilder('f')
            ->andWhere('f.name LIKE :name')
            ->setParameter('name', '%' . $name . '%')
            ->setMaxResults($limit)
            ->getQuery()
            ->getResult();
    }

    /**
     * Find all recipes (foods marked as recipes)
     */
    public function findAllRecipes(): array
    {
        return $this->createQueryBuilder('f')
            ->andWhere('f.isRecipe = :isRecipe')
            ->setParameter('isRecipe', true)
            ->orderBy('f.name', 'ASC')
            ->getQuery()
            ->getResult();
    }

    /**
     * Find recipes by category
     */
    public function findRecipesByCategory(string $category): array
    {
        return $this->createQueryBuilder('f')
            ->andWhere('f.isRecipe = :isRecipe')
            ->setParameter('isRecipe', true)
            ->andWhere('f.category = :category')
            ->setParameter('category', $category)
            ->orderBy('f.name', 'ASC')
            ->getQuery()
            ->getResult();
    }
}
