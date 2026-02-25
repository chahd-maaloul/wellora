<?php

namespace App\Repository;

use App\Entity\User;
use App\Enum\UserRole;
use App\Entity\Patient;
use App\Entity\Medecin;
use App\Entity\Coach;
use App\Entity\Nutritionist;
use App\Entity\Administrator;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\Security\Core\Exception\UnsupportedUserException;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\PasswordUpgraderInterface;

class UserRepository extends ServiceEntityRepository implements PasswordUpgraderInterface
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, User::class);
    }

    public function findOneByEmail(string $email): ?User
    {
        return $this->createQueryBuilder('u')
            ->andWhere('u.email = :email')
            ->setParameter('email', $email)
            ->getQuery()
            ->getOneOrNullResult();
    }

    public function findOneByUuid(string $uuid): ?User
    {
        return $this->createQueryBuilder('u')
            ->andWhere('u.uuid = :uuid')
            ->setParameter('uuid', $uuid)
            ->getQuery()
            ->getOneOrNullResult();
    }

    public function findOneByResetToken(string $token): ?User
    {
        $now = new \DateTime();
        
        return $this->createQueryBuilder('u')
            ->andWhere('u.resetToken = :token')
            ->andWhere('u.resetTokenExpiresAt > :now')
            ->setParameter('token', $token)
            ->setParameter('now', $now)
            ->getQuery()
            ->getOneOrNullResult();
    }

    public function findOneByLicenseNumber(string $licenseNumber): ?User
    {
        return $this->createQueryBuilder('u')
            ->andWhere('u.licenseNumber = :licenseNumber')
            ->setParameter('licenseNumber', $licenseNumber)
            ->getQuery()
            ->getOneOrNullResult();
    }

    public function findActiveUsers(): array
    {
        return $this->createQueryBuilder('u')
            ->andWhere('u.isActive = true')
            ->orderBy('u.createdAt', 'DESC')
            ->getQuery()
            ->getResult();
    }

    public function findLockedUsers(): array
    {
        return $this->createQueryBuilder('u')
            ->andWhere('u.lockedUntil IS NOT NULL')
            ->andWhere('u.lockedUntil > :now')
            ->setParameter('now', new \DateTime())
            ->getQuery()
            ->getResult();
    }

    public function countByRole(string $role): int
    {
        $repo = match ($role) {
            'App\Entity\Patient' => $this->getEntityManager()->getRepository(Patient::class),
            'App\Entity\Medecin' => $this->getEntityManager()->getRepository(Medecin::class),
            'App\Entity\Coach' => $this->getEntityManager()->getRepository(Coach::class),
            'App\Entity\Nutritionist' => $this->getEntityManager()->getRepository(Nutritionist::class),
            'App\Entity\Administrator' => $this->getEntityManager()->getRepository(Administrator::class),
            default => null,
        };
        
        if ($repo === null) {
            return 0;
        }
        
        return count($repo->findAll());
    }

    public function findAllWithFilters(?string $role = null, ?string $search = null, ?bool $isActive = null, ?bool $isVerified = null): array
    {
        // If role is specified, use the child repository
        if ($role !== null && $role !== 'all') {
            $repo = match ($role) {
                'ROLE_PATIENT' => $this->getEntityManager()->getRepository(Patient::class),
                'ROLE_MEDECIN' => $this->getEntityManager()->getRepository(Medecin::class),
                'ROLE_COACH' => $this->getEntityManager()->getRepository(Coach::class),
                'ROLE_NUTRITIONIST' => $this->getEntityManager()->getRepository(Nutritionist::class),
                'ROLE_ADMIN' => $this->getEntityManager()->getRepository(Administrator::class),
                default => null,
            };
            
            if ($repo !== null) {
                $users = $repo->findAll();
                
                // Apply additional filters in memory
                if ($search !== null && $search !== '') {
                    $searchLower = strtolower($search);
                    $users = array_filter($users, function($user) use ($searchLower) {
                        return stripos($user->getEmail(), $searchLower) !== false
                            || stripos($user->getFirstName(), $searchLower) !== false
                            || stripos($user->getLastName(), $searchLower) !== false;
                    });
                }
                
                if ($isActive !== null) {
                    $users = array_filter($users, function($user) use ($isActive) {
                        return $user->isIsActive() === $isActive;
                    });
                }
                
                if ($isVerified !== null) {
                    $users = array_filter($users, function($user) use ($isVerified) {
                        return $user->isEmailVerified() === $isVerified;
                    });
                }
                
                // Sort by createdAt DESC
                usort($users, function($a, $b) {
                    return $b->getCreatedAt() <=> $a->getCreatedAt();
                });
                
                return array_values($users);
            }
        }
        
        // No role filter - search all users
        $users = $this->findAll();
        
        // Apply additional filters in memory
        if ($search !== null && $search !== '') {
            $searchLower = strtolower($search);
            $users = array_filter($users, function($user) use ($searchLower) {
                return stripos($user->getEmail(), $searchLower) !== false
                    || stripos($user->getFirstName(), $searchLower) !== false
                    || stripos($user->getLastName(), $searchLower) !== false;
            });
        }
        
        if ($isActive !== null) {
            $users = array_filter($users, function($user) use ($isActive) {
                return $user->isIsActive() === $isActive;
            });
        }
        
        if ($isVerified !== null) {
            $users = array_filter($users, function($user) use ($isVerified) {
                return $user->isEmailVerified() === $isVerified;
            });
        }
        
        // Sort by createdAt DESC
        usort($users, function($a, $b) {
            return $b->getCreatedAt() <=> $a->getCreatedAt();
        });
        
        return array_values($users);
    }

    public function findUnverifiedProfessionals(): array
    {
        $medecins = $this->getEntityManager()->getRepository(Medecin::class)->findAll();
        $coaches = $this->getEntityManager()->getRepository(Coach::class)->findAll();
        $nutritionists = $this->getEntityManager()->getRepository(Nutritionist::class)->findAll();
        
        $unverified = [];
        
        foreach ($medecins as $medecin) {
            if (!$medecin->isVerifiedByAdmin()) {
                $unverified[] = $medecin;
            }
        }
        
        foreach ($coaches as $coach) {
            if (!$coach->isVerifiedByAdmin()) {
                $unverified[] = $coach;
            }
        }
        
        foreach ($nutritionists as $nutritionist) {
            if (!$nutritionist->isVerifiedByAdmin()) {
                $unverified[] = $nutritionist;
            }
        }
        
        // Sort by createdAt ASC
        usort($unverified, function($a, $b) {
            return $a->getCreatedAt() <=> $b->getCreatedAt();
        });
        
        return $unverified;
    }

    public function findByRole(UserRole $role): array
    {
        $repo = match ($role) {
            UserRole::PATIENT => $this->getEntityManager()->getRepository(Patient::class),
            UserRole::MEDECIN => $this->getEntityManager()->getRepository(Medecin::class),
            UserRole::COACH => $this->getEntityManager()->getRepository(Coach::class),
            UserRole::NUTRITIONIST => $this->getEntityManager()->getRepository(Nutritionist::class),
            UserRole::ADMIN => $this->getEntityManager()->getRepository(Administrator::class),
        };
        
        return $repo->findAll();
    }

    public function getUserStatistics(): array
    {
        return [
            'total' => count($this->findAll()),
            'patients' => count($this->getEntityManager()->getRepository(Patient::class)->findAll()),
            'medecins' => count($this->getEntityManager()->getRepository(Medecin::class)->findAll()),
            'coaches' => count($this->getEntityManager()->getRepository(Coach::class)->findAll()),
            'nutritionists' => count($this->getEntityManager()->getRepository(Nutritionist::class)->findAll()),
            'admins' => count($this->getEntityManager()->getRepository(Administrator::class)->findAll()),
            'active' => count(array_filter($this->findAll(), fn($u) => $u->isIsActive())),
            'inactive' => count(array_filter($this->findAll(), fn($u) => !$u->isIsActive())),
            'verified' => count(array_filter($this->findAll(), fn($u) => $u->isEmailVerified())),
            'unverified_professionals' => count($this->findUnverifiedProfessionals()),
        ];
    }

    public function upgradePassword(PasswordAuthenticatedUserInterface $user, string $newHashedPassword): void
    {
        if (!$user instanceof User) {
            throw new UnsupportedUserException(sprintf('Instances of "%s" are not supported.', $user::class));
        }

        $user->setPassword($newHashedPassword);
        $this->getEntityManager()->persist($user);
        $this->getEntityManager()->flush();
    }
}
