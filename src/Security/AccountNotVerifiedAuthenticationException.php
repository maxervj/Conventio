<?php

namespace App\Security;

use Symfony\Component\Security\Core\Exception\AccountStatusException;

class AccountNotVerifiedAuthenticationException extends AccountStatusException
{
    public function getMessageKey(): string
    {
        return 'Compte non validé. Cliquez sur le lien reçu dans votre boite mail.';
    }
}
