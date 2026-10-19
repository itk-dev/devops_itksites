<?php

declare(strict_types=1);

namespace App\Tests\Service;

use App\Entity\Advisory;
use App\Entity\Installation;
use App\Repository\AdvisoryRepository;
use App\Service\AdvisoryFactory;
use App\Service\PackageVersionFactory;
use Doctrine\ORM\EntityManagerInterface;
use Hautelook\AliceBundle\PhpUnit\RefreshDatabaseTrait;
use Psr\Log\LoggerInterface;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

/**
 * Runs the real factory against the test database.
 */
class AdvisoryFactoryTest extends KernelTestCase
{
    use RefreshDatabaseTrait;

    /**
     * The harvester can send a composer audit without a package list
     * ("packages": "unknown", as on srvitkleantime02), or with one that lacks
     * an audited package. One such advisory used to fail the whole result.
     */
    public function testSkipsAdvisoriesForUnlistedPackages(): void
    {
        self::bootKernel();
        $entityManager = self::getContainer()->get(EntityManagerInterface::class);
        $installation = $entityManager->getRepository(Installation::class)->findOneBy([]);
        self::getContainer()->get(PackageVersionFactory::class)->setPackageVersions($installation, [
            (object) ['name' => 'acme/listed_probe', 'description' => 'Probe', 'version' => '1.0.0'],
        ]);

        // Skipped advisories are logged, so known vulnerabilities are not silently dropped.
        $logger = $this->createMock(LoggerInterface::class);
        $logger->expects(self::once())->method('warning')->with(self::anything(), self::callback(
            static fn (array $context): bool => 'acme/unlisted_probe' === $context['package'] && 'PKSA-unlisted-probe' === $context['advisory'] && isset($context['installation'])
        ));
        $factory = new AdvisoryFactory($entityManager, self::getContainer()->get(AdvisoryRepository::class), $logger);

        $factory->setAdvisories($installation, (object) ['advisories' => (object) [
            'acme/unlisted_probe' => [$this->advisory('PKSA-unlisted-probe', 'acme/unlisted_probe')],
            'acme/listed_probe' => [$this->advisory('PKSA-listed-probe', 'acme/listed_probe')],
        ]]);
        $entityManager->flush();

        $advisories = $entityManager->getRepository(Advisory::class);
        $this->assertNull($advisories->findOneBy(['advisoryId' => 'PKSA-unlisted-probe']));
        $this->assertCount(1, $advisories->findOneBy(['advisoryId' => 'PKSA-listed-probe'])->getPackageVersions());
    }

    private function advisory(string $advisoryId, string $packageName): object
    {
        return (object) [
            'advisoryId' => $advisoryId,
            'packageName' => $packageName,
            'affectedVersions' => '<2.0.0',
            'title' => 'Probe',
            'cve' => null,
            'link' => 'https://example.com/'.$advisoryId,
            'reportedAt' => '2026-10-01T00:00:00+00:00',
            'sources' => [['name' => 'GitHub', 'remoteId' => $advisoryId]],
        ];
    }
}
