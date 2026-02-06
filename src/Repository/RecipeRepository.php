<?php

namespace App\Repository;

use App\Entity\Recipe;
use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Recipe>
 */
class RecipeRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Recipe::class);
    }

    /**
     * Find recipes for a user
     */
    public function findByUser(User $user): array
    {
        return $this->createQueryBuilder('r')
            ->andWhere('r.user = :user')
            ->andWhere('r.isDeleted = false')
            ->setParameter('user', $user)
            ->orderBy('r.createdAt', 'DESC')
            ->getQuery()
            ->getResult();
    }

    /**
     * Find a recipe by ID
     */
    public function findById(int $id): ?Recipe
    {
        return $this->createQueryBuilder('r')
            ->andWhere('r.id = :id')
            ->andWhere('r.isDeleted = false')
            ->setParameter('id', $id)
            ->getQuery()
            ->getOneOrNullResult();
    }

    /**
     * Search recipes by name
     */
    public function searchByName(User $user, string $searchTerm, int $limit = 20): array
    {
        return $this->createQueryBuilder('r')
            ->andWhere('r.user = :user')
            ->andWhere('r.name LIKE :search')
            ->andWhere('r.isDeleted = false')
            ->setParameter('user', $user)
            ->setParameter('search', '%' . $searchTerm . '%')
            ->setMaxResults($limit)
            ->getQuery()
            ->getResult();
    }

    /**
     * Find favorite recipes
     */
    public function findFavorites(User $user): array
    {
        return $this->createQueryBuilder('r')
            ->andWhere('r.user = :user')
            ->andWhere('r.isFavorite = true')
            ->andWhere('r.isDeleted = false')
            ->setParameter('user', $user)
            ->orderBy('r.name', 'ASC')
            ->getQuery()
            ->getResult();
    }

    /**
     * Find public recipes
     */
    public function findPublicRecipes(int $limit = 50): array
    {
        return $this->createQueryBuilder('r')
            ->andWhere('r.isPublic = true')
            ->andWhere('r.isDeleted = false')
            ->setMaxResults($limit)
            ->orderBy('r.createdAt', 'DESC')
            ->getQuery()
            ->getResult();
    }

    /**
     * Find most made recipes
     */
    public function findMostMade(User $user, int $limit = 10): array
    {
        return $this->createQueryBuilder('r')
            ->andWhere('r.user = :user')
            ->andWhere('r.isDeleted = false')
            ->setParameter('user', $user)
            ->orderBy('r.timesMade', 'DESC')
            ->setMaxResults($limit)
            ->getQuery()
            ->getResult();
    }

    /**
     * Find recipes by dietary info
     */
    public function findByDietaryInfo(User $user, array $dietaryInfo): array
    {
        $qb = $this->createQueryBuilder('r')
            ->andWhere('r.user = :user')
            ->andWhere('r.isDeleted = false')
            ->setParameter('user', $user);

        foreach ($dietaryInfo as $index => $info) {
            $qb->andWhere('r.dietaryInfo LIKE :info' . $index)
               ->setParameter('info' . $index, '%' . $info . '%');
        }

        return $qb->orderBy('r.name', 'ASC')
                  ->getQuery()
                  ->getResult();
    }

    /**
     * Find recipes by source URL
     */
    public function findBySourceUrl(string $sourceUrl): ?Recipe
    {
        return $this->createQueryBuilder('r')
            ->andWhere('r.sourceUrl = :sourceUrl')
            ->andWhere('r.isDeleted = false')
            ->setParameter('sourceUrl', $sourceUrl)
            ->getQuery()
            ->getOneOrNullResult();
    }

    /**
     * Get recent recipes for a user
     */
    public function findRecentRecipes(User $user, int $limit = 10): array
    {
        return $this->createQueryBuilder('r')
            ->andWhere('r.user = :user')
            ->andWhere('r.isDeleted = false')
            ->setParameter('user', $user)
            ->orderBy('r.createdAt', 'DESC')
            ->setMaxResults($limit)
            ->getQuery()
            ->getResult();
    }
}
