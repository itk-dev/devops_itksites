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
use Symfony\Component\Validator\Validator\ValidatorInterface;

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
        $factory = new AdvisoryFactory($entityManager, self::getContainer()->get(AdvisoryRepository::class), $logger, self::getContainer()->get(ValidatorInterface::class));

        $skipped = $factory->setAdvisories($installation, (object) ['advisories' => (object) [
            'acme/unlisted_probe' => [$this->advisory('PKSA-unlisted-probe', 'acme/unlisted_probe')],
            'acme/listed_probe' => [$this->advisory('PKSA-listed-probe', 'acme/listed_probe')],
        ]]);
        $entityManager->flush();

        $advisories = $entityManager->getRepository(Advisory::class);
        $this->assertNull($advisories->findOneBy(['advisoryId' => 'PKSA-unlisted-probe']));
        $this->assertCount(1, $skipped);
        $this->assertStringContainsString('PKSA-unlisted-probe', $skipped[0]);
        $this->assertCount(1, $advisories->findOneBy(['advisoryId' => 'PKSA-listed-probe'])->getPackageVersions());
    }

    /**
     * Symfony lists every affected minor back to 2.0; PKSA-b2gg-vk1g-6d5q's
     * constraint is 543 characters and failed the flush of the whole result.
     * Free-text fields are stored in full rather than skipped.
     */
    public function testStoresLongAffectedVersions(): void
    {
        self::bootKernel();
        $entityManager = self::getContainer()->get(EntityManagerInterface::class);
        $installation = $entityManager->getRepository(Installation::class)->findOneBy([]);
        self::getContainer()->get(PackageVersionFactory::class)->setPackageVersions($installation, [
            (object) ['name' => 'acme/long_probe', 'description' => 'Probe', 'version' => '1.0.0'],
        ]);
        $affectedVersions = implode('|', array_map(static fn (int $minor): string => sprintf('>=1.%d.0,<1.%d.0', $minor, $minor + 1), range(0, 79)));
        $this->assertGreaterThan(1000, strlen($affectedVersions));

        $advisory = $this->advisory('PKSA-long-probe', 'acme/long_probe');
        $advisory->affectedVersions = $affectedVersions;
        $advisory->title = str_repeat('Long title ', 50);
        $advisory->link = 'https://example.com/'.str_repeat('a', 500);
        $factory = new AdvisoryFactory($entityManager, self::getContainer()->get(AdvisoryRepository::class), $this->createStub(LoggerInterface::class), self::getContainer()->get(ValidatorInterface::class));
        $factory->setAdvisories($installation, (object) ['advisories' => (object) ['acme/long_probe' => [$advisory]]]);
        $entityManager->flush();
        $entityManager->clear();

        $stored = $entityManager->getRepository(Advisory::class)->findOneBy(['advisoryId' => 'PKSA-long-probe']);
        $this->assertSame($affectedVersions, $stored->getAffectedVersions());
        $this->assertSame($advisory->title, $stored->getTitle());
        $this->assertSame($advisory->link, $stored->getLink());
        $this->assertCount(1, $stored->getPackageVersions());
    }

    /**
     * The advisories of a result are flushed together, so one whose id or CVE
     * cannot fit must be skipped instead of failing the others, and logged as
     * an error, the lowest level prod writes.
     */
    public function testSkipsAdvisoriesThatDoNotFitAndStoresTheRest(): void
    {
        self::bootKernel();
        $entityManager = self::getContainer()->get(EntityManagerInterface::class);
        $installation = $entityManager->getRepository(Installation::class)->findOneBy([]);
        self::getContainer()->get(PackageVersionFactory::class)->setPackageVersions($installation, [
            (object) ['name' => 'acme/fit_probe', 'description' => 'Probe', 'version' => '1.0.0'],
        ]);

        $tooLong = $this->advisory('PKSA-too-long-probe', 'acme/fit_probe');
        $tooLong->cve = 'CVE-'.str_repeat('9', 50);
        $logger = $this->createMock(LoggerInterface::class);
        $logger->expects(self::once())->method('error')->with(self::anything(), self::callback(
            static fn (array $context): bool => 'PKSA-too-long-probe' === $context['advisory'] && str_contains($context['violations'], 'cve')
        ));
        $factory = new AdvisoryFactory($entityManager, self::getContainer()->get(AdvisoryRepository::class), $logger, self::getContainer()->get(ValidatorInterface::class));

        $skipped = $factory->setAdvisories($installation, (object) ['advisories' => (object) [
            'acme/fit_probe' => [$tooLong, $this->advisory('PKSA-fits-probe', 'acme/fit_probe')],
        ]]);
        $entityManager->flush();

        $advisories = $entityManager->getRepository(Advisory::class);
        $this->assertNull($advisories->findOneBy(['advisoryId' => 'PKSA-too-long-probe']));
        $this->assertCount(1, $skipped);
        $this->assertStringContainsString('PKSA-too-long-probe', $skipped[0]);
        $this->assertCount(1, $advisories->findOneBy(['advisoryId' => 'PKSA-fits-probe'])->getPackageVersions());
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
