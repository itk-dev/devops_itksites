<?php

namespace App\Types;

/**
 * Class DatabaseVersionType.
 */
class ServerTypeType
{
    public const CHOICES = [
        'Prod' => 'prod',
        'Stg' => 'stg',
        'DevOps' => 'devops',
    ];
}
