<?php

namespace App\Validator;

use Attribute;
use Symfony\Component\Validator\Constraint;

#[Attribute]
class ValidMedicalSpecialty extends Constraint
{
    public string $message = 'La spécialisation "{{ value }}" n\'est pas valide.';
}
