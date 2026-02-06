<?php

namespace App\Repository;

use App\Entity\BarcodeProduct;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<BarcodeProduct>
 */
class BarcodeProductRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, BarcodeProduct::class);
    }

    /**
     * Find a product by barcode
     */
    public function findByBarcode(string $barcode): ?BarcodeProduct
    {
        return $this->createQueryBuilder('b')
            ->andWhere('b.barcode = :barcode')
            ->andWhere('b.isDeleted = false')
            ->setParameter('barcode', $barcode)
            ->getQuery()
            ->getOneOrNullResult();
    }

    /**
     * Search products by name
     */
    public function searchByName(string $searchTerm, int $limit = 20): array
    {
        return $this->createQueryBuilder('b')
            ->andWhere('b.productName LIKE :search')
            ->andWhere('b.isDeleted = false')
            ->setParameter('search', '%' . $searchTerm . '%')
            ->setMaxResults($limit)
            ->getQuery()
            ->getResult();
    }

    /**
     * Find products by category
     */
    public function findByCategory(string $category): array
    {
        return $this->createQueryBuilder('b')
            ->andWhere('b.category = :category')
            ->andWhere('b.isDeleted = false')
            ->setParameter('category', $category)
            ->orderBy('b.productName', 'ASC')
            ->getQuery()
            ->getResult();
    }

    /**
     * Find most scanned products
     */
    public function findMostScanned(int $limit = 10): array
    {
        return $this->createQueryBuilder('b')
            ->andWhere('b.isDeleted = false')
            ->orderBy('b.scanCount', 'DESC')
            ->setMaxResults($limit)
            ->getQuery()
            ->getResult();
    }

    /**
     * Find verified products
     */
    public function findVerifiedProducts(): array
    {
        return $this->createQueryBuilder('b')
            ->andWhere('b.isVerified = true')
            ->andWhere('b.isDeleted = false')
            ->orderBy('b.productName', 'ASC')
            ->getQuery()
            ->getResult();
    }

    /**
     * Find unverified products
     */
    public function findUnverifiedProducts(): array
    {
        return $this->createQueryBuilder('b')
            ->andWhere('b.isVerified = false')
            ->andWhere('b.isDeleted = false')
            ->orderBy('b.createdAt', 'DESC')
            ->getQuery()
            ->getResult();
    }

    /**
     * Search products with category filter
     */
    public function searchWithCategory(?string $searchTerm, ?string $category, int $limit = 50): array
    {
        $qb = $this->createQueryBuilder('b')
            ->andWhere('b.isDeleted = false');

        if ($searchTerm !== null) {
            $qb->andWhere('b.productName LIKE :search')
               ->setParameter('search', '%' . $searchTerm . '%');
        }

        if ($category !== null) {
            $qb->andWhere('b.category = :category')
               ->setParameter('category', $category);
        }

        return $qb->setMaxResults($limit)
                  ->orderBy('b.productName', 'ASC')
                  ->getQuery()
                  ->getResult();
    }
}
