<?php

namespace App\Types;

class FrameworkTypes
{
    public const DRUPAL = 'drupal';
    public const NODE = 'node';
    public const SYMFONY = 'symfony';
    public const UNKNOWN = 'unknown';

    public const CHOICES = [
        'Drupal' => self::DRUPAL,
        'Node' => self::NODE,
        'Symfony' => self::SYMFONY,
        'Unknown' => self::UNKNOWN,
    ];
}
