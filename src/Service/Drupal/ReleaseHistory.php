<?php

declare(strict_types=1);

namespace App\Service\Drupal;

/**
 * A drupal.org project's release-history.
 */
final readonly class ReleaseHistory
{
    /**
     * @param array<string, Release> $releases keyed by Composer version
     */
    public function __construct(
        public array $releases,
    ) {
    }

    public function get(string $version): ?Release
    {
        return $this->releases[$version] ?? null;
    }
}
