<?php

namespace App\Entity;

use App\Enum\UserRole;
use App\Repository\AdministratorRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: AdministratorRepository::class)]
class Administrator extends User
{
    public function getDiscriminatorValue(): string
    {
        return UserRole::ADMIN->value;
    }
}
