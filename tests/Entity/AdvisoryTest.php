<?php

declare(strict_types=1);

namespace App\Tests\Entity;

use App\Entity\Advisory;
use PHPUnit\Framework\TestCase;

class AdvisoryTest extends TestCase
{
    public function testGetSourceLinks(): void
    {
        $advisory = new Advisory();
        $advisory->setSources([
            ['name' => 'GitHub', 'remoteId' => 'GHSA-mcrj-3wjf-3rmh'],
            ['name' => 'FriendsOfPHP/security-advisories', 'remoteId' => 'drupal/core/2018-10-17-1.yaml'],
            ['name' => 'Drupal core - Moderately critical - Cross Site Scripting - SA-CORE-2025-004', 'remoteId' => 'SA-CORE-2025-004'],
            ['name' => 'Some day', 'remoteId' => 'a-new-provider'],
        ]);

        $this->assertSame([
            'https://github.com/advisories/GHSA-mcrj-3wjf-3rmh',
            'https://github.com/FriendsOfPHP/security-advisories/blob/master/drupal/core/2018-10-17-1.yaml',
            'https://www.drupal.org/sa-core-2025-004',
            // Unknown sources get a label, not a URL; the template must not link it.
            'Some day / a-new-provider',
        ], $advisory->getSourceLinks());
    }
}
