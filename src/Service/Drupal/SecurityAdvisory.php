<?php

declare(strict_types=1);

namespace App\Service\Drupal;

/**
 * A drupal.org security advisory (SA-CORE-… / SA-CONTRIB-…).
 */
final readonly class SecurityAdvisory
{
    /**
     * @param string $affectedVersions as drupal.org states them, e.g. "<2.2.4"
     */
    public function __construct(
        public string $advisoryId,
        public string $url,
        public ?string $cve,
        public string $title,
        public string $affectedVersions,
        public \DateTimeImmutable $created,
    ) {
    }
}
