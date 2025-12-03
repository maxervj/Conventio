<?php

namespace App\Security;

use App\Entity\Student;
use Symfony\Component\Security\Core\Exception\CustomUserMessageAuthenticationException;
use Symfony\Component\Security\Core\User\UserCheckerInterface;
use Symfony\Component\Security\Core\User\UserInterface;

class UserChecker implements UserCheckerInterface
{
    private const REQUIRED_STUDENT_DOMAIN = '@lycee-faure.fr';

    public function checkPreAuth(UserInterface $user): void
    {
        if (!$user instanceof Student) {
            return;
        }

        // Check if student email has the required domain
        if (!str_ends_with(strtolower($user->getEmail()), self::REQUIRED_STUDENT_DOMAIN)) {
            throw new CustomUserMessageAuthenticationException(
                'Les étudiants doivent utiliser une adresse email "@lycee-faure.fr" pour se connecter.'
            );
        }

        // Check if student is verified
        if (!$user->isVerified()) {
            throw new AccountNotVerifiedAuthenticationException();
        }
    }

    public function checkPostAuth(UserInterface $user): void
    {
        // Vérifications après l'authentification si nécessaire
    }
}
