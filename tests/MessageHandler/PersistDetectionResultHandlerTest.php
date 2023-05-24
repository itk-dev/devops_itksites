<?php

namespace App\Tests\MessageHandler;

use App\Entity\DetectionResult;
use App\Entity\Server;
use App\Message\PersistDetectionResult;
use App\MessageHandler\PersistDetectionResultHandler;
use App\Repository\DetectionResultRepository;
use App\Repository\ServerRepository;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Uid\Ulid;

class PersistDetectionResultHandlerTest extends TestCase
{
    private ServerRepository $serverRepositoryMock;
    private DetectionResultRepository $detectionResultRepositoryMock;
    private EntityManagerInterface $entityManagerMock;
    private PersistDetectionResultHandler $handler;
    private DetectionResult $detectionResult;

    protected function setUp(): void
    {
        parent::setUp();

        $this->serverRepositoryMock = $this->createMock(ServerRepository::class);
        $this->detectionResultRepositoryMock = $this->createMock(DetectionResultRepository::class);
        $this->entityManagerMock = $this->createMock(EntityManagerInterface::class);

        $this->handler = new PersistDetectionResultHandler(
            $this->serverRepositoryMock,
            $this->detectionResultRepositoryMock,
            $this->entityManagerMock,
        );

        $this->detectionResult = new DetectionResult();
        $this->detectionResult->setType('dir');
        $this->detectionResult->setRootDir('/data/www/dir');
        $this->detectionResult->setData('string');

        $server = new Server();
        $server->setId(new Ulid('01H1447ZMTSY2JJ15B2MV362NS'));
        $this->serverRepositoryMock->expects($this->once())->method('findOneBy')
            ->willReturn($server);
    }

    public function testInvokeNewDetectionResult(): void
    {
        $apiKey = '12345678';
        $receivedAt = new \DateTimeImmutable();
        $detectionResultReceived = new PersistDetectionResult($this->detectionResult, $apiKey, $receivedAt);

        $this->detectionResultRepositoryMock->expects($this->once())->method('findOneBy')
            ->willReturn(null);
        $this->entityManagerMock->expects($this->once())->method('persist');

        $this->handler->__invoke($detectionResultReceived);

        $this->assertEquals($receivedAt, $this->detectionResult->getLastContact());
        $this->assertEquals('7db849eb2a64029b06748fec1724bb163cbe4cd9', $this->detectionResult->getHash());
    }

    public function testInvokeExistingDetectionResult(): void
    {
        $apiKey = '12345678';
        $receivedAt = new \DateTimeImmutable();
        $detectionResultReceived = new PersistDetectionResult($this->detectionResult, $apiKey, $receivedAt);

        $existingResult = new DetectionResult();
        $existingResult->setLastContact(\DateTimeImmutable::createFromFormat('Y-m-d', '2020-01-01'));

        $this->detectionResultRepositoryMock->expects($this->once())->method('findOneBy')
            ->willReturn($existingResult);

        // Persist should not be called if a matching (by server, hash) result is found
        $this->entityManagerMock->expects($this->never())->method('persist');

        $this->handler->__invoke($detectionResultReceived);

        // "lastContact" should only be updated for the existing result
        $this->assertEquals($receivedAt, $existingResult->getLastContact());
    }
}
