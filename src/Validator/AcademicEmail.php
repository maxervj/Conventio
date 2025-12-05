<?php

namespace App\Validator;

use Symfony\Component\Validator\Constraint;

#[\Attribute]
class AcademicEmail extends Constraint
{
    public string $message = 'L\'adresse email "{{ value }}" n\'est pas une adresse académique valide. Veuillez utiliser une adresse @ac-grenoble.fr';
    public string $mode = 'strict';
}
