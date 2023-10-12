<?php

declare(strict_types=1);

namespace App\Message;

use Symfony\Component\Uid\Ulid;

final class ProcessDetectionResult
{
    public function __construct(
        public readonly Ulid $detectionResultId
    ) {
    }
}
