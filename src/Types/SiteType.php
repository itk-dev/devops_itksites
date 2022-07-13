<?php

namespace App\Types;

/**
 * Class SiteType.
 *
 * The types of site install.
 */
class SiteType
{
    public const NGINX = 'nginx';
    public const DOCKER = 'docker';

    public const CHOICES = [
        'Nginx' => self::NGINX,
        'Docker' => self::DOCKER,
    ];
}
