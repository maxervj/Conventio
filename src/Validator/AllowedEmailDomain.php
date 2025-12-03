<?php

namespace App\Validator;

use Symfony\Component\Validator\Constraint;

#[\Attribute]
class AllowedEmailDomain extends Constraint
{
    public string $message = 'L\'email ne respecte pas le format autorisé. Vous devez avoir un email au format {{ allowed_domain }}';
    public array $allowedDomains = [];

    public function __construct(array $allowedDomains = null, array $groups = null, mixed $payload = null)
    {
        parent::__construct([], $groups, $payload);
        $this->allowedDomains = $allowedDomains ?? [];
    }
}
