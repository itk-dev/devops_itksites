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
            ['name' => 'Drupal', 'remoteId' => 'SA-CORE-2023-006'],
        ]);

        $this->assertSame([
            'https://github.com/advisories/GHSA-mcrj-3wjf-3rmh',
            'https://github.com/FriendsOfPHP/security-advisories/blob/master/drupal/core/2018-10-17-1.yaml',
            // Unknown sources get a label, not a URL; the template must not link it.
            'Drupal / SA-CORE-2023-006',
        ], $advisory->getSourceLinks());
    }
}
