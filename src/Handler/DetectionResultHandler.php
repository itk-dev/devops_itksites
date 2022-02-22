<?php

namespace App\Handler;

use App\Entity\DetectionResult;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Messenger\Handler\MessageHandlerInterface;

final class DetectionResultHandler implements MessageHandlerInterface
{
    public function __construct(private EntityManagerInterface $entityManager)
    {
    }

    public function __invoke(DetectionResult $detectionResult)
    {
        $d = 1;

        // @TODO process
    }
}
