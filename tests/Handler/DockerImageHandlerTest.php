<?php

declare(strict_types=1);

namespace App\Tests\Handler;

use App\Entity\Advisory;
use App\Entity\DetectionResult;
use App\Entity\Installation;
use App\Entity\PackageVersion;
use App\Entity\Server;
use App\Handler\DockerImageHandler;
use App\Message\ProcessDetectionResult;
use App\MessageHandler\ProcessDetectionResultHandler;
use App\Types\DetectionType;
use Doctrine\ORM\EntityManagerInterface;
use Hautelook\AliceBundle\PhpUnit\RefreshDatabaseTrait;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class DockerImageHandlerTest extends KernelTestCase
{
    use RefreshDatabaseTrait;

    private const string ROOT_DIR = '/data/www/fallback_probe/htdocs';

    private EntityManagerInterface $entityManager;

    protected function setUp(): void
    {
        self::bootKernel();
        $this->entityManager = self::getContainer()->get(EntityManagerInterface::class);
    }

    public function testModulesUpdatePackagesWhenPackagesAreUnknown(): void
    {
        $this->handle(['installed' => [['name' => 'drupal/fallback_probe', 'description' => 'Probe', 'version' => '6.2.9']]], '6.2.9');
        $this->handle('unknown', '6.2.12');

        $this->assertSame(['6.2.12'], $this->installedVersions());
    }

    public function testPackagesWinOverModules(): void
    {
        $this->handle(['installed' => [['name' => 'drupal/fallback_probe', 'description' => 'Probe', 'version' => '6.2.9']]], '6.2.12');

        $this->assertSame(['6.2.9'], $this->installedVersions());
    }

    /**
     * An advisory that cannot be stored must not fail the others, and the
     * result must say so instead of only logging it. Reprocessing clears it.
     */
    public function testSkippedAdvisoriesMarkTheDetectionResult(): void
    {
        $advisory = static fn (string $id, string $cve): array => [
            'advisoryId' => $id, 'packageName' => 'acme/audit_probe', 'affectedVersions' => '<2.0.0', 'title' => 'Probe',
            'cve' => $cve, 'link' => 'https://example.com/'.$id, 'reportedAt' => '2026-11-04T00:00:00+00:00', 'sources' => [],
        ];
        $detectionResult = $this->persist([
            'domain' => 'audit-probe.example.com',
            'containers' => [[
                'name' => 'phpfpm',
                'image' => 'itkdev/php8.4-fpm',
                'packages' => ['installed' => [['name' => 'acme/audit_probe', 'description' => 'Probe', 'version' => '1.0.0']]],
                'audit' => ['advisories' => ['acme/audit_probe' => [
                    $advisory('PKSA-unfit-probe', 'CVE-'.str_repeat('9', 50)),
                    $advisory('PKSA-fit-probe', 'CVE-2026-1'),
                ]]],
                'drupal' => 'unknown',
                'symfony' => 'unknown',
            ]],
        ]);
        $process = self::getContainer()->get(ProcessDetectionResultHandler::class);

        $process(new ProcessDetectionResult($detectionResult->getId()));

        $stored = $this->entityManager->getRepository(DetectionResult::class)->find($detectionResult->getId());
        $this->assertCount(1, $stored->getProcessingErrors());
        $this->assertStringContainsString('PKSA-unfit-probe', $stored->getProcessingErrors()[0]);
        $this->assertNotNull($this->entityManager->getRepository(Advisory::class)->findOneBy(['advisoryId' => 'PKSA-fit-probe']));
        $this->assertNull($this->entityManager->getRepository(Advisory::class)->findOneBy(['advisoryId' => 'PKSA-unfit-probe']));

        // Reprocessing once the audit no longer has the unfit advisory clears the mark.
        $stored->setData(str_replace('PKSA-unfit-probe', 'PKSA-fit-probe', $stored->getData()));
        $stored->setData((string) preg_replace('/"cve":"CVE-9+"/', '"cve":"CVE-2026-1"', $stored->getData()));
        $this->entityManager->flush();
        $process(new ProcessDetectionResult($detectionResult->getId()));

        $this->assertSame([], $this->entityManager->getRepository(DetectionResult::class)->find($detectionResult->getId())->getProcessingErrors());
    }

    /**
     * @param array<string, mixed>|string $packages
     */
    private function handle(array|string $packages, string $moduleVersion): void
    {
        $data = [
            'domain' => 'fallback-probe.example.com',
            'containers' => [[
                'name' => 'phpfpm',
                'image' => 'itkdev/php8.4-fpm',
                'packages' => $packages,
                'audit' => 'unknown',
                'drupal' => ['fallback_probe' => ['package' => 'Probe', 'status' => 'Enabled', 'version' => $moduleVersion]],
                'symfony' => 'unknown',
            ]],
        ];

        $detectionResult = $this->persist($data);

        self::getContainer()->get(DockerImageHandler::class)->handleResult($detectionResult);
        $this->entityManager->flush();
    }

    /**
     * @param array<string, mixed> $data
     */
    private function persist(array $data): DetectionResult
    {
        $detectionResult = new DetectionResult();
        $detectionResult->setType(DetectionType::DOCKER);
        $detectionResult->setRootDir(self::ROOT_DIR);
        $detectionResult->setServer($this->entityManager->getRepository(Server::class)->findOneBy([]));
        $detectionResult->setData(json_encode($data, JSON_THROW_ON_ERROR));
        $detectionResult->generateHash();
        $detectionResult->setLastContact();
        $this->entityManager->persist($detectionResult);
        $this->entityManager->flush();

        return $detectionResult;
    }

    /**
     * @return list<string>
     */
    private function installedVersions(): array
    {
        $this->entityManager->clear();
        $installation = $this->entityManager->getRepository(Installation::class)->findOneBy(['rootDir' => self::ROOT_DIR]);

        $versions = [];
        /** @var PackageVersion $packageVersion */
        foreach ($installation->getPackageVersions() as $packageVersion) {
            if ('fallback_probe' === $packageVersion->getPackage()->getName()) {
                $versions[] = $packageVersion->getVersion();
            }
        }

        return $versions;
    }
}
