<?php

declare(strict_types=1);

namespace App\Message;

use App\Entity\DetectionResult;

final readonly class PersistDetectionResult
{
    public function __construct(
        public DetectionResult $detectionResult,
        public string $serverApiKey,
        public \DateTimeImmutable $receivedAt,
    ) {
    }
}
