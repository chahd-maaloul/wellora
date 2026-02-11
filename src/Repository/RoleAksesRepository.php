<?php

namespace App\Repository;

use App\Entity\RoleAkses;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<RoleAkses>
 *
 * @method RoleAkses|null find($id, $lockMode = null, $lockVersion = null)
 * @method RoleAkses|null findOneBy(array $criteria, array $orderBy = null)
 * @method RoleAkses[]    findAll()
 * @method RoleAkses[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class RoleAksesRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, RoleAkses::class);
    }

    /**
     * Find roles by names
     */
    public function findByNames(array $names): array
    {
        return $this->createQueryBuilder('r')
            ->where('r.name IN (:names)')
            ->setParameter('names', $names)
            ->getQuery()
            ->getResult();
    }

    /**
     * Find KAUR roles
     */
    public function findKaurRoles(): array
    {
        return $this->findByNames(RoleAkses::KAUR_ROLES);
    }

    /**
     * Check if role exists by name
     */
    public function existsByName(string $name): bool
    {
        return $this->count(['name' => $name]) > 0;
    }
}
