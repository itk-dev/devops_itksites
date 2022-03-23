<?php

namespace App\Types;

/**
 * Class DatabaseVersionType.
 */
class DatabaseVersionType
{
    public const CHOICES = [
        'MySQL 5.5' => '5.5',
        'MySQL 5.7' => '5.7',
        'Mariadb 10.0' => '10.0',
        'Mariadb 10.2' => '10.2',
        'Mariadb 10.3' => '10.3',
        'Mariadb 10.5' => '10.5',
        'Mariadb 10.6' => '10.6',
    ];
}
