<?php

declare(strict_types=1);

namespace App\Security;

use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use ItkDev\OpenIdConnect\Exception\ItkOpenIdConnectException;
use ItkDev\OpenIdConnectBundle\Exception\InvalidProviderException;
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
     *
     * @param EntityManagerInterface $entityManager
     * @param UrlGeneratorInterface $router
     * @param OpenIdConfigurationProviderManager $providerManager
     */
    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly UrlGeneratorInterface $router,
        OpenIdConfigurationProviderManager $providerManager,
    ) {
        parent::__construct($providerManager);
    }

    /** {@inheritDoc} */
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
        } catch (ItkOpenIdConnectException|InvalidProviderException $exception) {
            throw new CustomUserMessageAuthenticationException($exception->getMessage());
        }
    }

    /** {@inheritDoc} */
    public function onAuthenticationSuccess(Request $request, TokenInterface $token, string $firewallName): ?Response
    {
        $targetUrl = $this->getTargetPath($request->getSession(), $firewallName) ?? $this->router->generate('admin');

        return new RedirectResponse($targetUrl);
    }

    /** {@inheritDoc} */
    public function start(Request $request, ?AuthenticationException $authException = null): Response
    {
        return new RedirectResponse($this->router->generate('itkdev_openid_connect_login', [
            'providerKey' => 'azure_az',
        ]));
    }
}
