<?php

declare(strict_types=1);

namespace App\Utils;

/**
 * The drupal.org project a module belongs to, i.e. the drupal/<project> package.
 */
class DrupalProject
{
    public const string CORE = 'drupal';
    private const string CORE_PACKAGE = 'core';

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

    /**
     * The project a drupal/<name> package ships. Core is the one package not
     * named after its project.
     */
    public static function fromPackageName(string $name): string
    {
        return self::CORE_PACKAGE === $name ? self::CORE : $name;
    }

    /**
     * The name of the drupal/<name> package a project ships as.
     */
    public static function toPackageName(string $project): string
    {
        return self::CORE === $project ? self::CORE_PACKAGE : $project;
    }
}
