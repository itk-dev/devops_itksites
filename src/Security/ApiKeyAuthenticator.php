<?php

declare(strict_types=1);

namespace App\Security;

use App\Repository\ServerRepository;
use App\Repository\UserRepository;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Core\Exception\BadCredentialsException;
use Symfony\Component\Security\Core\Exception\CustomUserMessageAuthenticationException;
use Symfony\Component\Security\Core\User\InMemoryUser;
use Symfony\Component\Security\Http\Authenticator\AbstractAuthenticator;
use Symfony\Component\Security\Http\Authenticator\Passport\Badge\UserBadge;
use Symfony\Component\Security\Http\Authenticator\Passport\Passport;
use Symfony\Component\Security\Http\Authenticator\Passport\SelfValidatingPassport;

class ApiKeyAuthenticator extends AbstractAuthenticator
{
    public const string AUTH_HEADER = 'Authorization';
    public const string AUTH_HEADER_PREFIX = 'Apikey ';
    public const string DEPLOYER_IDENTIFIER = 'woodpecker';

    public function __construct(
        private readonly ServerRepository $serverRepository,
        private readonly UserRepository $userRepository,
        #[Autowire('%env(APP_WOODPECKER_API_KEY)%')]
        private readonly string $woodpeckerApiKey,
    ) {
    }

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

    public function authenticate(Request $request): Passport
    {
        $apiKey = substr((string) $request->headers->get(self::AUTH_HEADER), strlen(self::AUTH_HEADER_PREFIX));
        if ('' === $apiKey) {
            // The token header was empty, authentication fails with HTTP Status
            // Code 401 "Unauthorized"
            throw new CustomUserMessageAuthenticationException('No API token provided');
        }

        // The deployment pipeline authenticates with a key held in the
        // environment rather than in the database, so that rotating it does not
        // touch our data and can later be moved to the vault. It is not a user
        // or a server: it may only report deployments, so it carries neither
        // ROLE_USER nor ROLE_SERVER.
        if ('' !== $this->woodpeckerApiKey && hash_equals($this->woodpeckerApiKey, $apiKey)) {
            return new SelfValidatingPassport(
                new UserBadge(
                    self::DEPLOYER_IDENTIFIER,
                    static fn (string $identifier): InMemoryUser => new InMemoryUser($identifier, null, ['ROLE_DEPLOYER']),
                )
            );
        }

        // Users and servers can authenticate to use the API.
        $apiUser = $this->serverRepository->findOneBy(['apiKey' => $apiKey])
            ?? $this->userRepository->findOneBy(['apiKey' => $apiKey]);
        if (null !== $apiUser) {
            return new SelfValidatingPassport(new UserBadge($apiUser->getUserIdentifier()));
        }

        throw new BadCredentialsException('Invalid credentials.');
    }

    public function onAuthenticationSuccess(Request $request, TokenInterface $token, string $firewallName): ?Response
    {
        // on success, let the request continue
        return null;
    }

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
