<?php

declare(strict_types=1);

namespace App\Utils;

/**
 * The drupal.org project a module belongs to, i.e. the drupal/<project> package.
 */
class DrupalProject
{
    public const string CORE = 'drupal';

    /**
     * drush reports the "project" key drupal.org packaging writes into
     * .info.yml. Composer-installed core has no such key, so core modules are
     * recognised by their path instead; their drush "package" varies.
     */
    public static function fromDrush(?string $project, ?string $path): ?string
    {
        if (null !== $project && '' !== $project) {
            return $project;
        }

        return str_starts_with($path ?? '', 'core/') ? self::CORE : null;
    }
}
