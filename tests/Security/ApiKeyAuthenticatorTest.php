<?php

declare(strict_types=1);

namespace App\Tests\Security;

use App\Repository\ServerRepository;
use App\Repository\UserRepository;
use App\Security\ApiKeyAuthenticator;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Exception\BadCredentialsException;

class ApiKeyAuthenticatorTest extends TestCase
{
    private ServerRepository $serverRepositoryStub;
    private UserRepository $userRepositoryStub;

    protected function setUp(): void
    {
        parent::setUp();

        $this->serverRepositoryStub = $this->createStub(ServerRepository::class);
        $this->userRepositoryStub = $this->createStub(UserRepository::class);

        $this->serverRepositoryStub->method('findOneBy')->willReturn(null);
        $this->userRepositoryStub->method('findOneBy')->willReturn(null);
    }

    public function testTheConfiguredDeployKeyAuthenticates(): void
    {
        $passport = $this->authenticator('s3cr3t')->authenticate($this->request('s3cr3t'));

        $user = $passport->getUser();

        $this->assertSame(ApiKeyAuthenticator::DEPLOYER_IDENTIFIER, $user->getUserIdentifier());
        $this->assertSame(['ROLE_DEPLOYER'], $user->getRoles());
    }

    /**
     * Deploy reporting is disabled by leaving the key unset, and an unset key
     * must not turn into a key that anything can match.
     */
    public function testAnUnconfiguredDeployKeyNeverAuthenticates(): void
    {
        $this->expectException(BadCredentialsException::class);

        $this->authenticator('')->authenticate($this->request('anything at all'));
    }

    public function testAWrongKeyIsRejected(): void
    {
        $this->expectException(BadCredentialsException::class);

        $this->authenticator('s3cr3t')->authenticate($this->request('not-the-key'));
    }

    private function authenticator(string $woodpeckerApiKey): ApiKeyAuthenticator
    {
        return new ApiKeyAuthenticator(
            $this->serverRepositoryStub,
            $this->userRepositoryStub,
            $woodpeckerApiKey,
        );
    }

    private function request(string $apiKey): Request
    {
        $request = new Request();
        $request->headers->set(
            ApiKeyAuthenticator::AUTH_HEADER,
            ApiKeyAuthenticator::AUTH_HEADER_PREFIX.$apiKey,
        );

        return $request;
    }
}
