<?php

declare(strict_types=1);

namespace App\Tests\MessageHandler;

use App\Entity\DetectionResult;
use App\Entity\Server;
use App\Message\PersistDetectionResult;
use App\Message\ProcessDetectionResult;
use App\MessageHandler\PersistDetectionResultHandler;
use App\Repository\DetectionResultRepository;
use App\Repository\ServerRepository;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\MockObject\MockObject;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\Messenger\Envelope;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Uid\Ulid;

class PersistDetectionResultHandlerTest extends KernelTestCase
{
    private ServerRepository&MockObject $serverRepositoryMock;
    private DetectionResultRepository&MockObject $detectionResultRepositoryMock;
    private EntityManagerInterface&MockObject $entityManagerMock;
    private MessageBusInterface&MockObject $messageBusMock;

    private PersistDetectionResultHandler $handler;
    private DetectionResult $detectionResult;

    protected function setUp(): void
    {
        parent::setUp();

        self::bootKernel();

        $this->serverRepositoryMock = $this->createMock(ServerRepository::class);
        $this->detectionResultRepositoryMock = $this->createMock(DetectionResultRepository::class);
        $this->entityManagerMock = $this->createMock(EntityManagerInterface::class);
        $this->messageBusMock = $this->createMock(MessageBusInterface::class);

        $this->handler = new PersistDetectionResultHandler(
            $this->serverRepositoryMock,
            $this->detectionResultRepositoryMock,
            $this->entityManagerMock,
            $this->messageBusMock,
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
        $this->detectionResult->setId(new Ulid());

        $apiKey = '12345678';
        $receivedAt = new \DateTimeImmutable();
        $detectionResultReceived = new PersistDetectionResult($this->detectionResult, $apiKey, $receivedAt);

        $this->detectionResultRepositoryMock->expects($this->once())->method('findOneBy')
            ->willReturn(null);
        $this->entityManagerMock->expects($this->once())->method('persist');
        $this->messageBusMock->expects($this->once())->method('dispatch')
            ->willReturn(new Envelope(new ProcessDetectionResult(new Ulid())));

        $this->handler->__invoke($detectionResultReceived);

        $this->assertEquals($receivedAt, $this->detectionResult->getLastContact());
        $this->assertEquals('7db849eb2a64029b06748fec1724bb163cbe4cd9', $this->detectionResult->getHash());
    }

    public function testInvokeExistingDetectionResult(): void
    {
        $this->detectionResult->setType('docker');

        $apiKey = '12345678';
        $receivedAt = new \DateTimeImmutable();
        $detectionResultReceived = new PersistDetectionResult($this->detectionResult, $apiKey, $receivedAt);

        $existingResult = new DetectionResult();
        $existingResult->setLastContact(\DateTimeImmutable::createFromFormat('Y-m-d', '2020-01-01'));

        $this->detectionResultRepositoryMock->expects($this->once())->method('findOneBy')
            ->willReturn($existingResult);

        // Persist should not be called if a matching (by server, hash) result is found
        $this->entityManagerMock->expects($this->never())->method('persist');

        // Process message should not be sent if a matching (by server, hash) result is found
        $this->messageBusMock->expects($this->never())->method('dispatch');

        $this->handler->__invoke($detectionResultReceived);

        // "lastContact" should only be updated for the existing result
        $this->assertEquals($receivedAt, $existingResult->getLastContact());
    }

    public function testInvokeExistingDirDetectionResultIsReprocessed(): void
    {
        $receivedAt = new \DateTimeImmutable();
        $detectionResultReceived = new PersistDetectionResult($this->detectionResult, '12345678', $receivedAt);

        $existingResult = new DetectionResult();
        $existingResult->setId(new Ulid());
        $existingResult->setType('dir');

        $this->detectionResultRepositoryMock->expects($this->once())->method('findOneBy')
            ->willReturn($existingResult);
        $this->entityManagerMock->expects($this->never())->method('persist');

        // The dir list decides which installations exist, so it is re-applied on every harvest.
        $this->messageBusMock->expects($this->once())->method('dispatch')
            ->with(new ProcessDetectionResult($existingResult->getId()))
            ->willReturn(new Envelope(new ProcessDetectionResult(new Ulid())));

        $this->handler->__invoke($detectionResultReceived);

        $this->assertEquals($receivedAt, $existingResult->getLastContact());
    }
}
