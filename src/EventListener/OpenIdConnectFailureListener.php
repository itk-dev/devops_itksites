<?php

declare(strict_types=1);

namespace App\EventListener;

use ItkDev\OpenIdConnectBundle\Exception\AuthenticationFailedException;
use ItkDev\OpenIdConnectBundle\Exception\ProviderErrorException;
use Symfony\Component\EventDispatcher\Attribute\AsEventListener;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\ExceptionEvent;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Twig\Environment;

/**
 * Renders a failed OIDC login as a page rather than as an unhandled exception.
 *
 * A failed callback throws `AuthenticationFailedException`, which is deliberately
 * not an `AuthenticationException`: the firewall does not catch it and does not
 * send the browser back to Azure, which is what turned the expired client secret
 * into a redirect loop. What escapes the firewall instead is an unhandled
 * exception, so without this listener a failed login shows the default error page.
 *
 * `ProviderErrorException` is the subclass Azure sends when it refuses the request
 * — a cancelled consent screen, an expired session there, a tenant policy. It
 * carries the status the answer should have, and the page says the login was
 * declined rather than that something went wrong.
 *
 * Only `AuthenticationFailedException` is handled. The bundle's other failures
 * are configuration bugs — an unknown provider key, an unreachable
 * `metadata_url` — and they keep the error page that says so.
 */
#[AsEventListener(
    event: KernelEvents::EXCEPTION,
    method: 'onKernelException',
    // Below Symfony's ErrorListener::logKernelException (0), so the failure is
    // still logged, and above its onKernelException (-128), whose rendering this
    // replaces: setting a response stops propagation, so that one never runs.
    priority: -64,
)]
readonly class OpenIdConnectFailureListener
{
    public function __construct(
        private Environment $twig,
        private UrlGeneratorInterface $urlGenerator,
    ) {
    }

    public function onKernelException(ExceptionEvent $event): void
    {
        $exception = $event->getThrowable();

        if (!$event->isMainRequest() || !$exception instanceof AuthenticationFailedException) {
            return;
        }

        // Deliberately without the exception message: it carries the identity
        // provider's own error text, which belongs in the log, not in a browser.
        // `declined` is the one thing worth telling the user, and it is the error
        // code rather than that text.
        $content = $this->twig->render('error/openid_connect_failed.html.twig', [
            'login_url' => $this->urlGenerator->generate('itkdev_openid_connect_login', ['providerKey' => 'azure_az']),
            'declined' => $exception instanceof ProviderErrorException
                && ProviderErrorException::ACCESS_DENIED === $exception->getError(),
        ]);

        // A refusal states its own status — 403 where the user or a policy said
        // no, 503 where Azure reports its own trouble. Everything else is a 500:
        // the likely cause is on this side of the login, and it should read as an
        // error in the log and in monitoring.
        $status = $exception instanceof HttpExceptionInterface
            ? $exception->getStatusCode()
            : Response::HTTP_INTERNAL_SERVER_ERROR;

        $response = new Response($content, $status);
        $response->headers->set('Cache-Control', 'no-store, private');

        $event->setResponse($response);
    }
}
