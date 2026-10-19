<?php

declare(strict_types=1);

namespace App\Tests\Utils;

use App\Utils\DrupalProject;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

class DrupalProjectTest extends TestCase
{
    #[DataProvider('drushProvider')]
    public function testFromDrush(?string $project, ?string $path, ?string $expected): void
    {
        self::assertSame($expected, DrupalProject::fromDrush($project, $path));
    }

    /**
     * @return iterable<string, array{?string, ?string, ?string}>
     */
    public static function drushProvider(): iterable
    {
        yield 'main module' => ['key_auth', 'modules/contrib/key_auth', 'key_auth'];
        yield 'submodule' => ['webform', 'modules/contrib/webform/modules/webform_ui', 'webform'];
        yield 'core module' => ['', 'core/modules/node', 'drupal'];
        yield 'core module, missing project' => [null, 'core/modules/text', 'drupal'];
        yield 'git install' => ['', 'modules/contrib/os2forms', null];
        yield 'nothing known' => [null, null, null];
    }
}
