<?php

declare(strict_types=1);

namespace App\Types;

class FrameworkTypes
{
    public const DRUPAL = 'drupal';
    public const NODE = 'node';
    public const SYMFONY = 'symfony';
    public const PYTHON = 'python';
    public const UNKNOWN = 'unknown';

    public const CHOICES = [
        'Drupal' => self::DRUPAL,
        'Node' => self::NODE,
        'Symfony' => self::SYMFONY,
        'Python' => self::PYTHON,
        'Unknown' => self::UNKNOWN,
    ];
}
