<?php

declare(strict_types=1);

namespace App\Security;

use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use ItkDev\OpenIdConnect\Exception\OpenIdConnectExceptionInterface;
use ItkDev\OpenIdConnectBundle\Security\OpenIdConfigurationProviderManager;
use ItkDev\OpenIdConnectBundle\Security\OpenIdLoginAuthenticator;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Core\Exception\CustomUserMessageAuthenticationException;
use Symfony\Component\Security\Http\Authenticator\Passport\Badge\UserBadge;
use Symfony\Component\Security\Http\Authenticator\Passport\Passport;
use Symfony\Component\Security\Http\Authenticator\Passport\SelfValidatingPassport;
use Symfony\Component\Security\Http\Util\TargetPathTrait;

class AzureOIDCAuthenticator extends OpenIdLoginAuthenticator
{
    use TargetPathTrait;

    /**
     * AzureOIDCAuthenticator constructor.
     */
    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly UrlGeneratorInterface $router,
        OpenIdConfigurationProviderManager $providerManager,
    ) {
        parent::__construct($providerManager);
    }

    public function authenticate(Request $request): Passport
    {
        try {
            // Validate claims
            $claims = $this->validateClaims($request);

            // Extract properties from claims
            $name = $claims['name'];
            $email = $claims['upn'];

            // Check if user exists already - if not create a user
            $user = $this->entityManager->getRepository(User::class)->findOneBy(['email' => $email]);

            if (null === $user) {
                // Create the new user and persist it
                $user = new User($name, $email, ['ROLE_ADMIN']);
                $this->entityManager->persist($user);
            } else {
                // Update/set user properties
                $user->setName($name);
                $user->setEmail($email);
            }

            $this->entityManager->flush();

            return new SelfValidatingPassport(new UserBadge($user->getUserIdentifier()));
        } catch (OpenIdConnectExceptionInterface $exception) {
            // Chained: the bundle reads the cause back in onAuthenticationFailure()
            // to decide what the user is shown. Dropping it turns a refusal the
            // user caused into an unexplained 500.
            throw new CustomUserMessageAuthenticationException($exception->getMessage(), previous: $exception);
        }
    }

    public function onAuthenticationSuccess(Request $request, TokenInterface $token, string $firewallName): ?Response
    {
        $targetUrl = $this->getTargetPath($request->getSession(), $firewallName) ?? $this->router->generate('admin');

        return new RedirectResponse($targetUrl);
    }

    public function start(Request $request, ?AuthenticationException $authException = null): Response
    {
        return new RedirectResponse($this->router->generate('itkdev_openid_connect_login', [
            'providerKey' => 'azure_az',
        ]));
    }
}
