<?php

declare(strict_types=1);

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
        'Mariadb 10.1' => '10.1',
        'Mariadb 10.2' => '10.2',
        'Mariadb 10.3' => '10.3',
        'Mariadb 10.4' => '10.4',
        'Mariadb 10.5' => '10.5',
        'Mariadb 10.6' => '10.6',
        'Mariadb 10.7' => '10.7',
        'Mariadb 10.8' => '10.8',
        'Mariadb 10.9' => '10.9',
        'Mariadb 10.10' => '10.10',
        'Mariadb 10.11' => '10.11',
        'Mariadb 11.0' => '11.0',
        'Mariadb 11.1' => '11.1',
        'Mariadb 11.2' => '11.3',
        'Mariadb 11.4' => '11.4',
        'Mariadb 11.5' => '11.5',
        'Mariadb 11.6' => '11.6',
    ];
}
