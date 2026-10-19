<?php

declare(strict_types=1);

namespace App\Tests\Entity;

use App\Entity\Installation;
use Doctrine\ORM\EntityManagerInterface;
use Hautelook\AliceBundle\PhpUnit\RefreshDatabaseTrait;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class InstallationTest extends KernelTestCase
{
    use RefreshDatabaseTrait;

    /**
     * A git checkout reports its version with the commit, e.g. Drupal core on
     * deltag.aarhus.dk, which failed every detection result from 2026-10-14.
     */
    public function testStoresGitCheckoutVersions(): void
    {
        self::bootKernel();
        $entityManager = self::getContainer()->get(EntityManagerInterface::class);
        $installation = $entityManager->getRepository(Installation::class)->findOneBy([]);

        $installation
            ->setFrameworkVersion('11.3.x-dev f509c89')
            ->setComposerVersion('2.9.999-dev+3f1a2b4c')
            ->setPhpVersion('8.5.0-dev');
        $entityManager->flush();
        $entityManager->clear();

        $installation = $entityManager->getRepository(Installation::class)->find($installation->getId());
        $this->assertSame('11.3.x-dev f509c89', $installation->getFrameworkVersion());
        $this->assertSame('2.9.999-dev+3f1a2b4c', $installation->getComposerVersion());
    }
}
