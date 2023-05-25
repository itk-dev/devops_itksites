<?php

namespace App\Message;

use App\Entity\DetectionResult;

final class PersistDetectionResult
{
    public function __construct(
        public readonly DetectionResult $detectionResult,
        public readonly string $serverApiKey,
        public readonly \DateTimeImmutable $receivedAt,
    ) {
    }
}
