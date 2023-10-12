<?php

declare(strict_types=1);

namespace App\Types;

/**
 * Class SiteType.
 *
 * The types of site install.
 */
class GitClonedByType
{
    public const HTTPS = 'https';
    public const SSH = 'ssh';
    public const UNKNOWN = 'unknown';

    public const CHOICES = [
        'HTTPS' => self::HTTPS,
        'SSH' => self::SSH,
        'UNKNOWN' => self::UNKNOWN,
    ];
}
