<?php

declare(strict_types=1);

namespace App\Types;

class DetectionType
{
    public const DIRECTORY = 'dir';
    public const DOCKER = 'docker';
    public const DRUPAL = 'drupal';
    public const GIT = 'git';
    public const NGINX = 'nginx';
    public const SYMFONY = 'symfony';

    public const CHOICES = [
        'Directory' => self::DIRECTORY,
        'Docker' => self::DOCKER,
        'Drupal' => self::DRUPAL,
        'Git' => self::GIT,
        'Nginx' => self::NGINX,
        'Symfony' => self::SYMFONY,
    ];
}
