<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="App\Repository\RoleAksesRepository")
 * @ORM\Table(name="role_akses")
 */
class RoleAkses
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=255, unique=true)
     */
    private $name;

    // KAUR Roles Constant
    public const KAUR_ROLES = [
        'ADMIN',
        'KADES',
        'SEKDES',
        'KAUR PERENCANAAN',
        'KAUR KEUANGAN',
        'KAUR TATA USAHA & UMUM'
    ];

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): self
    {
        $this->name = $name;

        return $this;
    }

    /**
     * Check if the role is a KAUR role
     */
    public function isKaurRole(): bool
    {
        return in_array($this->name, self::KAUR_ROLES);
    }

    /**
     * Get all KAUR roles
     */
    public static function getKaurRoles(): array
    {
        return self::KAUR_ROLES;
    }
}
