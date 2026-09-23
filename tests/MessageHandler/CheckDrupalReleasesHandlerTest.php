<?php

declare(strict_types=1);

namespace App\Tests\MessageHandler;

use App\Entity\Installation;
use App\Entity\Module;
use App\Entity\ModuleVersion;
use App\Entity\Package;
use App\Entity\PackageVersion;
use App\Message\CheckDrupalPackageRelease;
use App\Message\CheckDrupalReleases;
use App\MessageHandler\CheckDrupalReleasesHandler;
use Doctrine\ORM\EntityManagerInterface;
use Hautelook\AliceBundle\PhpUnit\RefreshDatabaseTrait;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\Messenger\Envelope;
use Symfony\Component\Messenger\Transport\InMemory\InMemoryTransport;

class CheckDrupalReleasesHandlerTest extends KernelTestCase
{
    use RefreshDatabaseTrait;

    public function testLinksThenFansOutPerDrupalPackage(): void
    {
        self::bootKernel();
        $container = self::getContainer();
        $entityManager = $container->get(EntityManagerInterface::class);

        $drupal = new Package()->setVendor('drupal')->setName('fanout_probe');
        $packageVersion = new PackageVersion()->setVersion('1.0.0');
        $drupal->addPackageVersion($packageVersion);
        $other = new Package()->setVendor('acme')->setName('fanout_probe');
        $otherVersion = new PackageVersion()->setVersion('1.0.0');
        $other->addPackageVersion($otherVersion);
        $module = new Module()->setName('fanout_probe')->setPackage('Other')->setEnabled(true);
        $moduleVersion = new ModuleVersion()->setVersion('1.0.0');
        $module->addModuleVersion($moduleVersion);
        foreach ([$drupal, $packageVersion, $other, $otherVersion, $module, $moduleVersion] as $entity) {
            $entityManager->persist($entity);
        }
        // Rows no installation uses are deleted on flush.
        $entityManager->getRepository(Installation::class)->findOneBy([])
            ->addPackageVersion($packageVersion)
            ->addPackageVersion($otherVersion)
            ->addModuleVersion($moduleVersion);
        $entityManager->flush();

        $container->get(CheckDrupalReleasesHandler::class)(new CheckDrupalReleases());

        $this->assertSame($packageVersion, $moduleVersion->getComposerPackageVersion());

        $expected = array_map(
            static fn (Package $package): string => (string) $package->getId(),
            $entityManager->getRepository(Package::class)->findBy(['vendor' => 'drupal']),
        );
        /** @var InMemoryTransport $transport */
        $transport = $container->get('messenger.transport.async');
        $messages = array_map(static fn (Envelope $envelope): object => $envelope->getMessage(), $transport->getSent());
        $this->assertContainsOnlyInstancesOf(CheckDrupalPackageRelease::class, $messages);
        $sent = array_map(static fn (CheckDrupalPackageRelease $message): string => (string) $message->packageId, $messages);
        $this->assertContains((string) $drupal->getId(), $sent);
        $this->assertNotContains((string) $other->getId(), $sent);
        $this->assertEqualsCanonicalizing($expected, $sent);
    }
}
