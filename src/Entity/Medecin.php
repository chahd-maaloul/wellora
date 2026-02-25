<?php

namespace App\Entity;

use App\Enum\UserRole;
use App\Repository\MedecinRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: MedecinRepository::class)]
class Medecin extends User
{
    public const SPECIALTY_CARDIOLOGY = 'CARDIOLOGY';
    public const SPECIALTY_DERMATOLOGY = 'DERMATOLOGY';
    public const SPECIALTY_ENDOCRINOLOGY = 'ENDOCRINOLOGY';
    public const SPECIALTY_NEUROLOGY = 'NEUROLOGY';
    public const SPECIALTY_PSYCHIATRY = 'PSYCHIATRY';
    public const SPECIALTY_PHYSIOTHERAPY = 'PHYSIOTHERAPY';
    public const SPECIALTY_PEDIATRICS = 'PEDIATRICS';
    public const SPECIALTY_GYNECOLOGY = 'GYNECOLOGY';
    public const SPECIALTY_OPHTHALMOLOGY = 'OPHTHALMOLOGY';
    public const SPECIALTY_OTHER = 'OTHER';

    public const MEDICAL_SPECIALTIES = [
        self::SPECIALTY_CARDIOLOGY => 'Cardiologie',
        self::SPECIALTY_DERMATOLOGY => 'Dermatologie',
        self::SPECIALTY_ENDOCRINOLOGY => 'Endocrinologie',
        self::SPECIALTY_NEUROLOGY => 'Neurologie',
        self::SPECIALTY_PSYCHIATRY => 'Psychiatrie',
        self::SPECIALTY_PHYSIOTHERAPY => 'Physiothérapie',
        self::SPECIALTY_PEDIATRICS => 'Pédiatrie',
        self::SPECIALTY_GYNECOLOGY => 'Gynécologie',
        self::SPECIALTY_OPHTHALMOLOGY => 'Ophtalmologie',
        self::SPECIALTY_OTHER => 'Autre',
    ];

    public function getDiscriminatorValue(): string
    {
        return UserRole::MEDECIN->value;
    }
}
