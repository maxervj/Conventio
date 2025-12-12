<?php

namespace App\EventListener;

use App\Service\LoginAttemptService;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Security\Http\Event\LoginFailureEvent;
use Symfony\Component\Security\Http\Event\LoginSuccessEvent;

class LoginAttemptListener implements EventSubscriberInterface
{
    public function __construct(
        private LoginAttemptService $loginAttemptService
    ) {
    }

    public static function getSubscribedEvents(): array
    {
        return [
            LoginFailureEvent::class => 'onLoginFailure',
            LoginSuccessEvent::class => 'onLoginSuccess',
        ];
    }

    public function onLoginFailure(LoginFailureEvent $event): void
    {
        $request = $event->getRequest();
        $email = $request->request->get('_username');

        if ($email) {
            $this->loginAttemptService->recordFailedAttempt($email);
        }
    }

    public function onLoginSuccess(LoginSuccessEvent $event): void
    {
        $user = $event->getUser();

        if (method_exists($user, 'getEmail')) {
            $this->loginAttemptService->recordSuccessfulAttempt($user->getEmail());
        }
    }
}
