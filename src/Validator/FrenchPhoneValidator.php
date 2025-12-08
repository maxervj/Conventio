<?php

namespace App\Validator;

use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;
use Symfony\Component\Validator\Exception\UnexpectedValueException;

class FrenchPhoneValidator extends ConstraintValidator
{
    public function validate(mixed $value, Constraint $constraint): void
    {
        if (!$constraint instanceof FrenchPhone) {
            throw new UnexpectedTypeException($constraint, FrenchPhone::class);
        }

        if (null === $value || '' === $value) {
            return;
        }

        if (!is_string($value)) {
            throw new UnexpectedValueException($value, 'string');
        }

        // Remove spaces, dots, dashes
        $phone = preg_replace('/[\s\.\-]/', '', $value);

        // Check French landline format: 0[1-5]XXXXXXXX
        $isLandline = preg_match('/^0[1-5]\d{8}$/', $phone);

        // Check French mobile format: 0[67]XXXXXXXX
        $isMobile = preg_match('/^0[67]\d{8}$/', $phone);

        // Check international format: +33[1-9]XXXXXXXX
        $isInternational = preg_match('/^\+33[1-9]\d{8}$/', $phone);

        $valid = false;

        if ($constraint->allowLandline && $isLandline) {
            $valid = true;
        }

        if ($constraint->allowMobile && $isMobile) {
            $valid = true;
        }

        if ($isInternational) {
            $valid = true;
        }

        if (!$valid) {
            $this->context->buildViolation($constraint->message)
                ->setParameter('{{ value }}', $value)
                ->addViolation();
        }
    }
}
