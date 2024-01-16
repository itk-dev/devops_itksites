<?php

declare(strict_types=1);

namespace App\EventListener;

use Symfony\Component\EventDispatcher\Attribute\AsEventListener;
use Symfony\Component\Security\Http\Event\LoginSuccessEvent;

#[AsEventListener(event: LoginSuccessEvent::class, method: 'onLogin')]
class LoginSuccessEventListener
{
    public function onLogin(LoginSuccessEvent $event): void
    {
        $session = $event->getRequest()->getSession();
        $session->set('confetti', true);
    }
}
