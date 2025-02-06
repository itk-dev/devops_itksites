<?php

declare(strict_types=1);

namespace App\Tests\MessageHandler;

use App\Entity\DetectionResult;
use App\Handler\DetectionResultHandlerInterface;
use App\Message\ProcessDetectionResult;
use App\MessageHandler\ProcessDetectionResultHandler;
use App\Repository\DetectionResultRepository;
use App\Types\DetectionType;
use Doctrine\DBAL\Connection;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Uid\Ulid;

class ProcessDetectionResultHandlerTest extends TestCase
{
    private DetectionResultRepository $detectionResultRepositoryMock;
    private EntityManagerInterface $entityManagerMock;
    private DetectionResultHandlerInterface $dirResultHandler;
    private DetectionResultHandlerInterface $nginxResultHandler;
    private ProcessDetectionResultHandler $handler;

    protected function setUp(): void
    {
        parent::setUp();

        $this->detectionResultRepositoryMock = $this->createMock(DetectionResultRepository::class);
        $this->entityManagerMock = $this->createMock(EntityManagerInterface::class);
        $connectionMock = $this->createMock(Connection::class);
        $this->entityManagerMock->expects($this->any())->method('getConnection')->willReturn($connectionMock);

        $this->dirResultHandler = $this->createMock(DetectionResultHandlerInterface::class);
        $this->nginxResultHandler = $this->createMock(DetectionResultHandlerInterface::class);

        $dirMap = [
            [DetectionType::DIRECTORY, true],
            [DetectionType::NGINX, false],
        ];
        $this->dirResultHandler->method('supportsType')->willReturnMap($dirMap);

        $nginxMap = [
            [DetectionType::DIRECTORY, false],
            [DetectionType::NGINX, true],
        ];
        $this->nginxResultHandler->method('supportsType')->willReturnMap($nginxMap);

        $this->handler = new ProcessDetectionResultHandler(
            $this->entityManagerMock,
            $this->detectionResultRepositoryMock,
            [$this->dirResultHandler, $this->nginxResultHandler],
            5
        );
    }

    public function testInvoke(): void
    {
        $message = new ProcessDetectionResult(new Ulid());

        $dirDetectionResult = new DetectionResult();
        $dirDetectionResult->setType(DetectionType::DIRECTORY);

        $this->detectionResultRepositoryMock->expects($this->once())->method('find')->willReturn($dirDetectionResult);

        $this->dirResultHandler->expects($this->once())->method('handleResult');
        $this->nginxResultHandler->expects($this->never())->method('handleResult');

        $this->handler->__invoke($message);
    }
}
