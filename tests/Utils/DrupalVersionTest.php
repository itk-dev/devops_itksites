<?php

declare(strict_types=1);

namespace App\Tests\Utils;

use App\Utils\DrupalVersion;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

class DrupalVersionTest extends TestCase
{
    #[DataProvider('versionProvider')]
    public function testToComposer(string $drupal, ?string $expected): void
    {
        self::assertSame($expected, DrupalVersion::toComposer($drupal));
    }

    /**
     * @return iterable<string, array{string, ?string}>
     */
    public static function versionProvider(): iterable
    {
        yield 'legacy' => ['8.x-1.19', '1.19.0'];
        yield 'legacy pre-release' => ['8.x-1.19-beta2', '1.19.0-beta2'];
        yield 'semver' => ['2.2.0', '2.2.0'];
        yield 'semver pre-release' => ['6.3.0-beta6', '6.3.0-beta6'];
        yield 'empty' => ['', null];
    }

    #[DataProvider('constraintProvider')]
    public function testConstraintToComposer(string $drupal, string $expected): void
    {
        self::assertSame($expected, DrupalVersion::constraintToComposer($drupal));
    }

    /**
     * @return iterable<string, array{string, string}>
     */
    public static function constraintProvider(): iterable
    {
        yield 'semver' => ['<2.2.4', '<2.2.4'];
        yield 'legacy' => ['<8.x-1.19', '<1.19.0'];
        yield 'legacy pre-release' => ['>=8.x-1.0-beta1 <8.x-1.19', '>=1.0.0-beta1 <1.19.0'];
        yield 'mixed' => ['<8.x-1.19 || >=2.0.0 <2.2.4', '<1.19.0 || >=2.0.0 <2.2.4'];
    }
}
