<?php

namespace App\Security;

use Symfony\Component\Security\Core\Exception\AccountStatusException;

class AccountNotVerifiedAuthenticationException extends AccountStatusException
{
    public function getMessageKey(): string
    {
        return 'Votre compte n\'a pas encore été vérifié. Veuillez consulter votre email et cliquer sur le lien de vérification.';
    }
}
