<?php

namespace App\Handler;

use App\Entity\DetectionResult;

interface DetectionResultHandlerInterface
{
    public function handleResult(DetectionResult $detectionResult): void;

    public function supportsType(string $type): bool;
}
