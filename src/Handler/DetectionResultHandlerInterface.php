<?php

namespace App\Handler;

use App\Entity\DetectionResult;

/**
 * DetectionResultHandlerInterface.
 *
 * All result handlers must implement this interface to enable proper processing
 * of result. This is used by the DI container to determine what services to inject
 */
interface DetectionResultHandlerInterface
{
    /**
     * Handle the detection result.
     */
    public function handleResult(DetectionResult $detectionResult): void;

    /**
     * Does the handler support the give detection result type.
     *
     * @param string $type
     *                     The result type
     */
    public function supportsType(string $type): bool;
}
