<?php

declare(strict_types=1);

namespace App\Types;

/**
 * Class DatabaseVersionType.
 */
class ServerTypeType
{
    public const PROD = 'prod';
    public const STG = 'stg';
    public const DEVOPS = 'devops';
    public const GPU = 'GPU';

    public const CHOICES = [
        'Prod' => self::PROD,
        'Stg' => self::STG,
        'DevOps' => self::DEVOPS,
        'GPU' => self::GPU,
    ];
}
