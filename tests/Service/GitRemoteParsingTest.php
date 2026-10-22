<?php

declare(strict_types=1);

namespace App\Tests\Service;

use App\Service\GitTagFactory;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

/**
 * Both the harvester and the Economics sync resolve a GitRepo through this
 * parser, so a remote and a hand-typed Economics URL for the same repo have
 * to come out as the same criteria.
 */
class GitRemoteParsingTest extends TestCase
{
    #[DataProvider('remotes')]
    public function testResolvesToLookupCriteria(string $remote, ?array $expected): void
    {
        self::assertSame($expected, GitTagFactory::parseRemote($remote));
    }

    /**
     * @return iterable<string, array{string, array{provider: string, organization: string, repo: string}|null}>
     */
    public static function remotes(): iterable
    {
        $dokk1gh = ['provider' => 'github.com', 'organization' => 'aakb', 'repo' => 'dokk1gh'];

        yield 'harvested clone url' => ['https://github.com/aakb/dokk1gh.git', $dokk1gh];
        yield 'ssh remote' => ['git@github.com:aakb/dokk1gh.git', $dokk1gh];
        yield 'plain url' => ['https://github.com/aakb/dokk1gh', $dokk1gh];
        yield 'no scheme' => ['github.com/aakb/dokk1gh', $dokk1gh];
        yield 'trailing slash' => ['https://github.com/aakb/dokk1gh/', $dokk1gh];
        yield 'mixed case' => ['https://GitHub.com/AAKB/Dokk1gh', $dokk1gh];
        yield 'surrounding whitespace' => ['  https://github.com/aakb/dokk1gh.git  ', $dokk1gh];
        yield 'dotted repo name' => [
            'https://github.com/itk-dev/deltag.aarhus.dk',
            ['provider' => 'github.com', 'organization' => 'itk-dev', 'repo' => 'deltag.aarhus.dk'],
        ];

        // The same repo name under another organization, or on another
        // provider, is a different row — a fork must not resolve to its
        // upstream.
        yield 'fork under another organization' => [
            'https://github.com/os2display/os2display-docker-server',
            ['provider' => 'github.com', 'organization' => 'os2display', 'repo' => 'os2display-docker-server'],
        ];
        yield 'same path on another provider' => [
            'https://gitlab.com/aakb/dokk1gh.git',
            ['provider' => 'gitlab.com', 'organization' => 'aakb', 'repo' => 'dokk1gh'],
        ];

        yield 'bare repo name' => ['dokk1gh', null];
        yield 'organization only' => ['https://github.com/aakb', null];
        yield 'deep path' => ['https://github.com/aakb/dokk1gh/tree/develop', null];
        yield 'empty' => ['', null];
    }
}
