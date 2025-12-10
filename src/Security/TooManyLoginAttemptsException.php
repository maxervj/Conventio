<?php

namespace App\Security;

use Symfony\Component\Security\Core\Exception\CustomUserMessageAuthenticationException;

class TooManyLoginAttemptsException extends CustomUserMessageAuthenticationException
{
    private int $minutesRemaining;

    public function __construct(int $minutesRemaining)
    {
        $this->minutesRemaining = $minutesRemaining;

        $message = sprintf(
            'Trop de tentatives de connexion. Veuillez réessayer dans %d minute%s.',
            $minutesRemaining,
            $minutesRemaining > 1 ? 's' : ''
        );

        parent::__construct($message);
    }

    public function getMinutesRemaining(): int
    {
        return $this->minutesRemaining;
    }
}
