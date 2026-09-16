<?php

declare(strict_types=1);

namespace App\Tests\MessageHandler;

use App\Entity\DeployResult;
use App\Entity\Server;
use App\MessageHandler\DeployResultHandler;
use App\Repository\DeployResultRepository;
use App\Repository\ServerRepository;
use App\Service\DeployResultApplier;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Uid\Ulid;

class DeployResultHandlerTest extends TestCase
{
    private ServerRepository $serverRepositoryStub;
    private DeployResultRepository $deployResultRepositoryStub;
    private DeployResultApplier $applierMock;
    private EntityManagerInterface $entityManagerMock;

    private DeployResultHandler $handler;

    protected function setUp(): void
    {
        parent::setUp();

        $this->serverRepositoryStub = $this->createStub(ServerRepository::class);
        $this->deployResultRepositoryStub = $this->createStub(DeployResultRepository::class);
        $this->applierMock = $this->createMock(DeployResultApplier::class);
        $this->entityManagerMock = $this->createMock(EntityManagerInterface::class);

        $this->handler = new DeployResultHandler(
            $this->serverRepositoryStub,
            $this->deployResultRepositoryStub,
            $this->applierMock,
            $this->entityManagerMock,
        );
    }

    public function testItPersistsAndAppliesANewDeployResult(): void
    {
        $this->serverRepositoryStub->method('findOneBy')->willReturn($this->server());
        $this->deployResultRepositoryStub->method('findOneBy')->willReturn(null);

        $this->entityManagerMock->expects($this->once())->method('persist');
        $this->applierMock->expects($this->once())->method('apply');

        $deployResult = $this->deployResult();

        $this->handler->__invoke($deployResult);

        $this->assertNotNull($deployResult->getHash());
        $this->assertNotNull($deployResult->getLastContact());
    }

    /**
     * Redeploying the same tag to the same place changes nothing, so only the
     * timestamp moves.
     */
    public function testItOnlyTouchesTheTimestampForAKnownDeployResult(): void
    {
        $existingResult = new DeployResult();
        $existingResult->setLastContact(\DateTimeImmutable::createFromFormat('Y-m-d', '2020-01-01'));

        $this->serverRepositoryStub->method('findOneBy')->willReturn($this->server());
        $this->deployResultRepositoryStub->method('findOneBy')->willReturn($existingResult);

        $this->entityManagerMock->expects($this->never())->method('persist');
        $this->applierMock->expects($this->never())->method('apply');

        $this->handler->__invoke($this->deployResult());

        $this->assertGreaterThan(
            \DateTimeImmutable::createFromFormat('Y-m-d', '2020-01-01'),
            $existingResult->getLastContact(),
        );
    }

    /**
     * The report was already answered with a 202, so an unknown server is
     * dropped rather than failing a deployment over our own bookkeeping.
     */
    public function testItDropsAReportForAnUnknownServer(): void
    {
        $this->serverRepositoryStub->method('findOneBy')->willReturn(null);

        $this->entityManagerMock->expects($this->never())->method('persist');
        $this->entityManagerMock->expects($this->never())->method('flush');
        $this->applierMock->expects($this->never())->method('apply');

        $this->handler->__invoke($this->deployResult());
    }

    private function server(): Server
    {
        $server = new Server();
        $server->setId(new Ulid('01H1447ZMTSY2JJ15B2MV362NS'));

        return $server;
    }

    private function deployResult(): DeployResult
    {
        $deployResult = new DeployResult();
        $deployResult->setServerName('srvitkphp84.itkdev.dk');
        $deployResult->setRootDir('/data/www/example/htdocs');
        $deployResult->setRepoUrl('https://github.com/itk-dev/example.git');
        $deployResult->setTag('1.4.2');

        return $deployResult;
    }
}
