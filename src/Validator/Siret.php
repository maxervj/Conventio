<?php

namespace App\Validator;

use Symfony\Component\Validator\Constraint;

#[\Attribute]
class Siret extends Constraint
{
    public string $message = 'The SIRET number "{{ value }}" is not valid.';
}
