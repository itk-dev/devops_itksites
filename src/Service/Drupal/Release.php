<?php

declare(strict_types=1);

namespace App\Service\Drupal;

/**
 * One release from drupal.org release-history.
 */
final readonly class Release
{
    /**
     * @param string       $version  Composer version (see DrupalVersion::toComposer())
     * @param list<string> $terms    "Release type" terms, e.g. "Security update", "Insecure"
     * @param bool         $security the release is a security update
     * @param bool         $insecure drupal.org marks the release insecure
     */
    public function __construct(
        public string $version,
        public array $terms,
        public bool $security,
        public bool $insecure,
    ) {
    }
}
