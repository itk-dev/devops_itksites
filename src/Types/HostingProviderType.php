<?php

declare(strict_types=1);

namespace App\Types;

/**
 * Class HostingProviderType.
 */
class HostingProviderType
{
    public const AZURE = 'Azure';
    public const DBC = 'DBC';
    public const IT_RELATION = 'It Relation';
    public const IT_RELATION_ADM = 'It Relation (ADM)';
    public const IT_RELATION_DMZ = 'It Relation (DMZ)';
    public const HETZNER = 'Hetzner';

    public const CHOICES = [
        'Azure' => self::AZURE,
        'DBC' => self::DBC,
        'ITR' => self::IT_RELATION,
        'ITR (ADM)' => self::IT_RELATION_ADM,
        'ITR (DMZ)' => self::IT_RELATION_DMZ,
        'Hetzner' => self::HETZNER,
    ];
}
