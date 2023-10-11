<?php

declare(strict_types=1);

namespace App\Types;

/**
 * Class SslProviderType.
 *
 * The different SSL providers available.
 */
class SslProviderType
{
    public const CHOICES = [
        'Aarhus' => 'aarhus',
        'Let\'s Encrypt' => 'letsencrypt',
    ];
}
