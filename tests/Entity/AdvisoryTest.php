<?php

declare(strict_types=1);

namespace App\Tests\Entity;

use App\Entity\Advisory;
use PHPUnit\Framework\TestCase;

class AdvisoryTest extends TestCase
{
    public function testGetAdvisoryUrl(): void
    {
        $advisory = new Advisory();

        $advisory->setAdvisoryId('PKSA-w7xr-vk7n-rstm');
        $this->assertSame('https://packagist.org/security-advisories/PKSA-w7xr-vk7n-rstm', $advisory->getAdvisoryUrl());

        // Advisories recorded under another issuer's id have no Packagist page.
        $advisory->setAdvisoryId('SA-CORE-2025-004');
        $this->assertNull($advisory->getAdvisoryUrl());
    }

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
