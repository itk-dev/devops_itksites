<?php

namespace App\EventListener;

use ItkDev\OpenIdConnectBundle\Security\OpenIdConfigurationProviderManager;
use Symfony\Component\EventDispatcher\Attribute\AsEventListener;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Security\Http\Event\LogoutEvent;

#[AsEventListener(event: LogoutEvent::class, method: 'onLogout')]
class LogoutEventListener
{
    public function __construct(
        private readonly OpenIdConfigurationProviderManager $providerManager,
        private readonly UrlGeneratorInterface $urlGenerator,
    ) {
    }

    public function onLogout(LogoutEvent $event): void
    {
        $provider = $this->providerManager->getProvider('azure_az');
        $postLogoutRedirectUri = $this->urlGenerator->generate('app_post_logout', [], UrlGeneratorInterface::ABSOLUTE_URL);
        $url = $provider->getEndSessionUrl($postLogoutRedirectUri);
        $redirect = new RedirectResponse($url);

        $event->setResponse($redirect);
    }
}
