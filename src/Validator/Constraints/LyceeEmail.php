<?php

namespace App\Validator\Constraints;

use Symfony\Component\Validator\Constraint;

#[\Attribute]
class LyceeEmail extends Constraint
{
    public string $message = 'Les étudiants doivent utiliser une adresse email "@lycee-faure.fr".';

    public function validatedBy(): string
    {
        return static::class.'Validator';
    }
}
