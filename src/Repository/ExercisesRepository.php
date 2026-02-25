<?php

namespace App\Repository;

use App\Entity\Exercises;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Exercises>
 */
class ExercisesRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Exercises::class);
    }

    /**
     * Trouve les exercices avec filtres et pagination
     */
    public function findByFilters($goalId = null, $category = null, $difficulty = null, $search = null, $offset = 0, $limit = 12)
    {
        $qb = $this->createQueryBuilder('e');
        
        // Filtre par objectif (goal)
        if ($goalId) {
            $qb->join('e.dailyPlans', 'dp')
               ->join('dp.goal', 'g')
               ->andWhere('g.id = :goalId')
               ->setParameter('goalId', $goalId);
        }
        
        // Filtre par catégorie
        if ($category && $category !== 'all') {
            $qb->andWhere('e.category = :category')
               ->setParameter('category', $category);
        }
        
        // Filtre par difficulté
        if ($difficulty && $difficulty !== 'all') {
            $qb->andWhere('e.difficulty_level = :difficulty')
               ->setParameter('difficulty', $difficulty);
        }
        
        // Recherche par nom ou description
        if ($search) {
            $qb->andWhere('e.name LIKE :search OR e.description LIKE :search')
               ->setParameter('search', '%' . $search . '%');
        }
        
        // Pagination et tri
        return $qb->orderBy('e.createdAt', 'DESC')
                  ->setFirstResult($offset)
                  ->setMaxResults($limit)
                  ->getQuery()
                  ->getResult();
    }

    /**
     * Compte le nombre total d'exercices avec les filtres
     */
    public function countByFilters($goalId = null, $category = null, $difficulty = null, $search = null)
    {
        $qb = $this->createQueryBuilder('e')
                   ->select('COUNT(e.id)');
        
        // Filtre par objectif (goal)
        if ($goalId) {
            $qb->join('e.dailyPlans', 'dp')
               ->join('dp.goal', 'g')
               ->andWhere('g.id = :goalId')
               ->setParameter('goalId', $goalId);
        }
        
        // Filtre par catégorie
        if ($category && $category !== 'all') {
            $qb->andWhere('e.category = :category')
               ->setParameter('category', $category);
        }
        
        // Filtre par difficulté
        if ($difficulty && $difficulty !== 'all') {
            $qb->andWhere('e.difficulty_level = :difficulty')
               ->setParameter('difficulty', $difficulty);
        }
        
        // Recherche par nom ou description
        if ($search) {
            $qb->andWhere('e.name LIKE :search OR e.description LIKE :search')
               ->setParameter('search', '%' . $search . '%');
        }
        
        return $qb->getQuery()->getSingleScalarResult();
    }

    /**
     * Récupère les exercices récents (pour le dashboard)
     */
    public function findRecentExercises(int $limit = 5)
    {
        return $this->createQueryBuilder('e')
            ->where('e.isActive = :active')
            ->setParameter('active', true)
            ->orderBy('e.createdAt', 'DESC')
            ->setMaxResults($limit)
            ->getQuery()
            ->getResult();
    }

    /**
     * Récupère les exercices par catégorie
     */
    public function findByCategory(string $category, int $limit = 10)
    {
        return $this->createQueryBuilder('e')
            ->where('e.category = :category')
            ->andWhere('e.isActive = :active')
            ->setParameter('category', $category)
            ->setParameter('active', true)
            ->orderBy('e.name', 'ASC')
            ->setMaxResults($limit)
            ->getQuery()
            ->getResult();
    }

    /**
     * Récupère les statistiques des catégories
     */
    public function getCategoryStats(): array
    {
        $qb = $this->createQueryBuilder('e')
            ->select('e.category, COUNT(e.id) as count')
            ->where('e.isActive = :active')
            ->setParameter('active', true)
            ->groupBy('e.category')
            ->orderBy('count', 'DESC');

        return $qb->getQuery()->getResult();
    }
}