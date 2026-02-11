<?php

namespace App\Validator;

use App\Enum\MedicalSpecialty;
use Symfony\Component\Validator\Attribute\HasNamedArguments;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;
use Symfony\Component\Validator\Exception\UnexpectedValueException;

class ValidMedicalSpecialtyValidator extends ConstraintValidator
{
    #[HasNamedArguments]
    public function __construct(
        private array $allowedSpecialties = []
    ) {
        $this->allowedSpecialties = array_map(fn($case) => $case->value, MedicalSpecialty::cases());
    }

    public function validate(mixed $value, Constraint $constraint): void
    {
        if (!$constraint instanceof ValidMedicalSpecialty) {
            throw new UnexpectedTypeException($constraint, ValidMedicalSpecialty::class);
        }

        if ($value === null || $value === '') {
            return;
        }

        if (!$value instanceof MedicalSpecialty) {
            throw new UnexpectedValueException($value, MedicalSpecialty::class);
        }

        if (!in_array($value->value, $this->allowedSpecialties, true)) {
            $this->context->buildViolation($constraint->message)
                ->setParameter('{{ value }}', $value->value)
                ->addViolation();
        }
    }
}
