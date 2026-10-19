<?php

declare(strict_types=1);

namespace App\Utils;

/**
 * Maps drupal.org module versions to the Composer versions they ship as.
 *
 * Legacy versions (8.x-1.19, 8.x-1.19-beta2) become 1.19.0 and 1.19.0-beta2;
 * semver versions (2.2.0, 6.3.0-beta6) are already identical.
 */
class DrupalVersion
{
    public static function toComposer(string $drupal): ?string
    {
        if ('' === $drupal) {
            return null;
        }

        return preg_replace('/^\d+\.x-(\d+)\.(\d+)(-.+)?$/', '$1.$2.0$3', $drupal);
    }

    /**
     * Maps every version in a drupal.org constraint, e.g. "<8.x-1.19 || >=2.0.0 <2.2.4".
     */
    public static function constraintToComposer(string $constraint): string
    {
        return (string) preg_replace('/\b\d+\.x-(\d+)\.(\d+)(-[0-9A-Za-z.]+)?/', '$1.$2.0$3', $constraint);
    }
}
