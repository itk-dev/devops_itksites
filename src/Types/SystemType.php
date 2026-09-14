<?php

declare(strict_types=1);

namespace App\Types;

/**
 * Class SystemType.
 *
 * The different OS's support in our server setup.
 *
 * Ubuntu LTS is even-numbered releases only, so only those are offered.
 */
class SystemType
{
    public const CHOICES = [
        'Ubuntu 16.04' => 'ubuntu1604',
        'Ubuntu 18.04' => 'ubuntu1804',
        'Ubuntu 20.04' => 'ubuntu2004',
        'Ubuntu 22.04' => 'ubuntu2204',
        'Ubuntu 24.04' => 'ubuntu2404',
        'Ubuntu 26.04' => 'ubuntu2604',
        'Debian 9' => 'deb9',
        'Debian 10' => 'deb10',
        'Debian 11' => 'deb11',
        'Debian 12' => 'deb12',
    ];
}
