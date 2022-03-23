<?php

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
        'Debian 9' => 'deb9',
        'Debian 10' => 'deb10',
    ];
}
