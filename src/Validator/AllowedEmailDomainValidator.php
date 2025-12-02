<?php

namespace App\Validator;

use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;

class AllowedEmailDomainValidator extends ConstraintValidator
{
    public function validate(mixed $value, Constraint $constraint): void
    {
        if (!$constraint instanceof AllowedEmailDomain) {
            throw new UnexpectedTypeException($constraint, AllowedEmailDomain::class);
        }

        if (null === $value || '' === $value) {
            return;
        }

        if (!is_string($value)) {
            throw new UnexpectedTypeException($value, 'string');
        }

        $emailParts = explode('@', $value);
        if (count($emailParts) !== 2) {
            return;
        }

        $domain = $emailParts[1];
        $isValid = false;

        foreach ($constraint->allowedDomains as $allowedDomain) {
            if (strtolower($domain) === strtolower($allowedDomain)) {
                $isValid = true;
                break;
            }
        }

        if (!$isValid) {
            $this->context->buildViolation($constraint->message)
                ->setParameter('{{ allowed_domain }}', '@' . implode(', @', $constraint->allowedDomains))
                ->addViolation();
        }
    }
}
