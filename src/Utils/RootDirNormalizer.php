<?php

declare(strict_types=1);

namespace App\Utils;

/**
 * Class RootDirNormalizer.
 *
 * The Server harvester will return various values for the same root dir depending
 * on context. E.g.
 * - /home/www/tjek1_aarhuskommune_dk/htdocs/public
 * - /home/www/tjek1_aarhuskommune_dk/htdocs
 * - /data/www/tjek1_aarhuskommune_dk/htdocs
 * all represent the same installation on the same server. We need to normalize
 * these to ensure we can use "rootdir" as a unique key.
 */
class RootDirNormalizer
{
    public static function normalize(string $rootDir): string
    {
        // Always return a "/data/..." rootdir for matching purposes
        // "/home/" points to "/data/" for the standard user in our setup
        if (\str_starts_with($rootDir, '/home/')) {
            $rootDir = substr($rootDir, 6);
            $rootDir = '/data/'.$rootDir;
        }

        // Ignore "/public" ending for matching purposes
        if (\str_ends_with($rootDir, '/public')) {
            $rootDir = \substr($rootDir, 0, -7);
        }

        return $rootDir;
    }
}
