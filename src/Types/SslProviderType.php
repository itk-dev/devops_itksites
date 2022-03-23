<?php

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
