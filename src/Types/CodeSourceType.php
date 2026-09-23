<?php

declare(strict_types=1);

namespace App\Types;

/**
 * Class CodeSourceType.
 *
 * Where the code in an installation came from. A site is either a git working
 * copy on disk, which the server harvester can inspect directly, or a release
 * artifact unpacked by a deployment, which carries no .git directory and is
 * therefore only known from what the deployment reports.
 */
class CodeSourceType
{
    public const GIT = 'git';
    public const ARTIFACT = 'artifact';

    public const CHOICES = [
        'Git' => self::GIT,
        'Artifact' => self::ARTIFACT,
    ];
}
