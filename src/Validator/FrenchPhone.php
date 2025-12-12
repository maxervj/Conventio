<?php

namespace App\Validator;

use Symfony\Component\Validator\Constraint;

#[\Attribute]
class FrenchPhone extends Constraint
{
    public string $message = 'The phone number "{{ value }}" is not valid. Expected format: 0X XX XX XX XX or +33 X XX XX XX XX';
    public bool $allowMobile = true;
    public bool $allowLandline = true;
}
