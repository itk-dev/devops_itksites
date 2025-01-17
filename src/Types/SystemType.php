<?php

declare(strict_types=1);

namespace App\Types;

/**
 * Class SystemType.
 *
 * The different OS's support in our server setup.
 */
class SystemType
{
    public const CHOICES = [
        'Ubuntu 16.04' => 'ubuntu1604',
        'Ubuntu 18.04' => 'ubuntu1804',
        'Ubuntu 20.04' => 'ubuntu2004',
        'Ubuntu 21.04' => 'ubuntu2104',
        'Ubuntu 22.04' => 'ubuntu2204',
        'Ubuntu 23.04' => 'ubuntu2304',
        'Ubuntu 24.04' => 'ubuntu2404',
        'Ubuntu 25.04' => 'ubuntu2504',
        'Debian 9' => 'deb9',
        'Debian 10' => 'deb10',
        'Debian 11' => 'deb11',
        'Debian 13' => 'deb12',
    ];
}
