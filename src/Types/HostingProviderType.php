<?php

namespace App\Types;

/**
 * Class HostingProviderType.
 */
class HostingProviderType
{
    public const AZURE = 'Azure';
    public const DBC = 'DBC';
    public const IT_RELATION = 'It Relation';

    public const CHOICES = [
        'Azure' => self::AZURE,
        'DBC' => self::DBC,
        'IT Relation' => self::IT_RELATION,
    ];
}
