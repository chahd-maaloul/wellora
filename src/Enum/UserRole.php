<?php

namespace App\Enum;

enum UserRole: string
{
    case PATIENT = 'ROLE_PATIENT';
    case MEDECIN = 'ROLE_MEDECIN';
    case COACH = 'ROLE_COACH';
    case NUTRITIONIST = 'ROLE_NUTRITIONIST';
    case ADMIN = 'ROLE_ADMIN';
}

enum MedicalSpecialty: string
{
    case CARDIOLOGY = 'CARDIOLOGY';
    case DERMATOLOGY = 'DERMATOLOGY';
    case ENDOCRINOLOGY = 'ENDOCRINOLOGY';
    case NEUROLOGY = 'NEUROLOGY';
    case PSYCHIATRY = 'PSYCHIATRY';
    case PHYSIOTHERAPY = 'PHYSIOTHERAPY';
    case PEDIATRICS = 'PEDIATRICS';
    case GYNECOLOGY = 'GYNECOLOGY';
    case OPHTHALMOLOGY = 'OPHTHALMOLOGY';
    case OTHER = 'OTHER';
}
