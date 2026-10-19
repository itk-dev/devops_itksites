<?php

declare(strict_types=1);

namespace App\Tests\Handler;

use App\Entity\DetectionResult;
use App\Entity\Installation;
use App\Entity\PackageVersion;
use App\Entity\Server;
use App\Handler\DrupalHandler;
use App\Types\DetectionType;
use Doctrine\ORM\EntityManagerInterface;
use Hautelook\AliceBundle\PhpUnit\RefreshDatabaseTrait;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class DrupalHandlerTest extends KernelTestCase
{
    use RefreshDatabaseTrait;

    private const string ROOT_DIR = '/data/www/fallback_probe/htdocs';

    private EntityManagerInterface $entityManager;

    protected function setUp(): void
    {
        self::bootKernel();
        $this->entityManager = self::getContainer()->get(EntityManagerInterface::class);
    }

    public function testModulesUpdatePackagesWhenPackagesAreMissing(): void
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
     * @param array<string, mixed>|string $packages
     */
    private function handle(array|string $packages, string $moduleVersion): void
    {
        $server = $this->entityManager->getRepository(Server::class)->findOneBy([]);
        $data = [
            'composerVersion' => '2',
            'version' => '10.6.17',
            'packages' => $packages,
            'modules' => ['fallback_probe' => ['package' => 'Probe', 'status' => 'Enabled', 'version' => $moduleVersion]],
        ];

        $detectionResult = new DetectionResult();
        $detectionResult->setType(DetectionType::DRUPAL);
        $detectionResult->setRootDir(self::ROOT_DIR);
        $detectionResult->setServer($server);
        $detectionResult->setData(json_encode($data, JSON_THROW_ON_ERROR));
        $detectionResult->generateHash();
        $detectionResult->setLastContact();
        $this->entityManager->persist($detectionResult);
        $this->entityManager->flush();

        self::getContainer()->get(DrupalHandler::class)->handleResult($detectionResult);
        $this->entityManager->flush();
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
