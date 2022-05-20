<?php

namespace App\Types;

/**
 * Class DatabaseVersionType.
 */
class ServerEnvType
{
    public const CHOICES = [
        'Prod' => 'prod',
        'Stg' => 'stg',
        'DevOps' => 'devops',
    ];
}
