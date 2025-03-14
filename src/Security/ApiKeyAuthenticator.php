<?php

declare(strict_types=1);

namespace App\Security;

use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Core\Exception\CustomUserMessageAuthenticationException;
use Symfony\Component\Security\Http\Authenticator\AbstractAuthenticator;
use Symfony\Component\Security\Http\Authenticator\Passport\Badge\UserBadge;
use Symfony\Component\Security\Http\Authenticator\Passport\Passport;
use Symfony\Component\Security\Http\Authenticator\Passport\SelfValidatingPassport;

class ApiKeyAuthenticator extends AbstractAuthenticator
{
    public const string AUTH_HEADER = 'Authorization';
    public const string AUTH_HEADER_PREFIX = 'Apikey ';

    /**
     * Called on every request to decide if this authenticator should be used for the request.
     *
     * Returning `false` will cause this authenticator to be skipped.
     */
    public function supports(Request $request): ?bool
    {
        return $request->headers->has(self::AUTH_HEADER)
            && str_starts_with((string) $request->headers->get(self::AUTH_HEADER), self::AUTH_HEADER_PREFIX);
    }

    /**
     * {@inheritdoc}
     */
    public function authenticate(Request $request): Passport
    {
        $apiKey = substr((string) $request->headers->get(self::AUTH_HEADER), strlen(self::AUTH_HEADER_PREFIX));
        if ('' === $apiKey) {
            // The token header was empty, authentication fails with HTTP Status
            // Code 401 "Unauthorized"
            throw new CustomUserMessageAuthenticationException('No API token provided');
        }

        return new SelfValidatingPassport(new UserBadge($apiKey));
    }

    /**
     * {@inheritdoc}
     */
    public function onAuthenticationSuccess(Request $request, TokenInterface $token, string $firewallName): ?Response
    {
        // on success, let the request continue
        return null;
    }

    /**
     * {@inheritdoc}
     */
    public function onAuthenticationFailure(Request $request, AuthenticationException $exception): ?Response
    {
        $data = [
            // you may want to customize or obfuscate the message first
            'message' => strtr($exception->getMessageKey(), $exception->getMessageData()),

            // or to translate this message
            // $this->translator->trans($exception->getMessageKey(), $exception->getMessageData())
        ];

        return new JsonResponse($data, Response::HTTP_UNAUTHORIZED);
    }
}
