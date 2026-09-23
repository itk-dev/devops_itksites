<?php

declare(strict_types=1);

namespace App\Tests\Command;

use App\Entity\Installation;
use App\Entity\Package;
use App\Entity\PackageVersion;
use App\Message\CheckDrupalReleases;
use App\Service\Drupal\ReleaseChecker;
use Doctrine\ORM\EntityManagerInterface;
use Hautelook\AliceBundle\PhpUnit\RefreshDatabaseTrait;
use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Tester\CommandTester;
use Symfony\Component\Messenger\Transport\InMemory\InMemoryTransport;
use Symfony\Contracts\Cache\CacheInterface;

class DrupalCheckReleasesCommandTest extends KernelTestCase
{
    use RefreshDatabaseTrait;

    public function testQueuesAllPackagesByDefault(): void
    {
        self::bootKernel();
        $checker = $this->createMock(ReleaseChecker::class);
        $checker->expects($this->never())->method('check');
        self::getContainer()->set(ReleaseChecker::class, $checker);

        $tester = $this->tester();
        $tester->execute([]);

        $tester->assertCommandIsSuccessful();
        /** @var InMemoryTransport $transport */
        $transport = self::getContainer()->get('messenger.transport.async');
        $this->assertCount(1, $transport->getSent());
        $this->assertInstanceOf(CheckDrupalReleases::class, $transport->getSent()[0]->getMessage());
    }

    public function testChecksOnePackageSynchronously(): void
    {
        self::bootKernel();
        $entityManager = self::getContainer()->get(EntityManagerInterface::class);
        $package = new Package()->setVendor('drupal')->setName('command_probe');
        $packageVersion = new PackageVersion()->setVersion('2.2.0');
        $package->addPackageVersion($packageVersion);
        $entityManager->persist($package);
        $entityManager->persist($packageVersion);
        // Rows no installation uses are deleted on flush.
        $entityManager->getRepository(Installation::class)->findOneBy([])->addPackageVersion($packageVersion);
        $entityManager->flush();

        $checker = $this->createMock(ReleaseChecker::class);
        $checker->expects($this->once())->method('check')->with($package)
            ->willReturnCallback(static function () use ($packageVersion): void {
                $packageVersion->setDrupalInsecure(true);
            });
        self::getContainer()->set(ReleaseChecker::class, $checker);

        $tester = $this->tester();
        $tester->execute(['--package' => 'drupal/command_probe']);

        $tester->assertCommandIsSuccessful();
        $this->assertMatchesRegularExpression('/2\.2\.0\s+insecure/', $tester->getDisplay());
        /** @var InMemoryTransport $transport */
        $transport = self::getContainer()->get('messenger.transport.async');
        $this->assertCount(0, $transport->getSent());
    }

    public function testUnknownPackageFails(): void
    {
        self::bootKernel();

        $tester = $this->tester();
        $tester->execute(['--package' => 'drupal/no_such_probe']);

        $this->assertSame(Command::FAILURE, $tester->getStatusCode());
        $this->assertStringContainsString('Package drupal/no_such_probe not found.', $tester->getDisplay());
    }

    public function testRefreshClearsTheCache(): void
    {
        self::bootKernel();
        /** @var CacheInterface $cache */
        $cache = self::getContainer()->get('cache.drupal_org');
        $cache->get('refresh_probe', static fn (): string => 'cached');

        $this->tester()->execute(['--package' => 'drupal/no_such_probe', '--refresh' => true]);

        $this->assertSame('fresh', $cache->get('refresh_probe', static fn (): string => 'fresh'));
    }

    private function tester(): CommandTester
    {
        return new CommandTester(new Application(self::$kernel)->find('itksites:drupal:check-releases'));
    }
}
