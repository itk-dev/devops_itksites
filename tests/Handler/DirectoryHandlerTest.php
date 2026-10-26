<?php

declare(strict_types=1);

namespace App\Tests\Handler;

use App\Entity\DetectionResult;
use App\Entity\Installation;
use App\Entity\Server;
use App\Entity\Site;
use App\Message\ProcessDetectionResult;
use App\MessageHandler\ProcessDetectionResultHandler;
use App\Types\DetectionType;
use Doctrine\ORM\EntityManagerInterface;
use Hautelook\AliceBundle\PhpUnit\RefreshDatabaseTrait;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class DirectoryHandlerTest extends KernelTestCase
{
    use RefreshDatabaseTrait;

    private const string KEPT_DIR = '/data/www/kept_probe/htdocs';
    private const string GONE_DIR = '/data/www/gone_probe/htdocs';

    private EntityManagerInterface $entityManager;
    private string $serverId;

    protected function setUp(): void
    {
        self::bootKernel();
        $this->entityManager = self::getContainer()->get(EntityManagerInterface::class);
        // A server without results of its own, so the tests decide which dir result is the latest.
        $this->serverId = (string) $this->entityManager->getRepository(Server::class)->findOneBy(['name' => 'gpu-01.example.com'])->getId();
    }

    public function testRemovesInstallationsNoLongerInDirResult(): void
    {
        $this->process(DetectionType::DIRECTORY, '/data/www', [self::KEPT_DIR, self::GONE_DIR], new \DateTimeImmutable('-1 day'));
        $this->process(DetectionType::DOCKER, self::KEPT_DIR, $this->dockerData('kept-probe.example.com'));
        $goneDockerResultId = $this->process(DetectionType::DOCKER, self::GONE_DIR, $this->dockerData('gone-probe.example.com'));

        $this->assertNotNull($this->findInstallation(self::GONE_DIR));

        $this->process(DetectionType::DIRECTORY, '/data/www', [self::KEPT_DIR]);

        $this->assertNull($this->findInstallation(self::GONE_DIR));
        $this->assertNull($this->entityManager->getRepository(Site::class)->findOneBy(['rootDir' => self::GONE_DIR]));
        $this->assertNull($this->entityManager->getRepository(DetectionResult::class)->find($goneDockerResultId));

        $this->assertNotNull($this->findInstallation(self::KEPT_DIR));
        $this->assertNotNull($this->entityManager->getRepository(Site::class)->findOneBy(['rootDir' => self::KEPT_DIR]));
    }

    public function testIgnoresDirResultsOlderThanTheLatest(): void
    {
        $this->process(DetectionType::DIRECTORY, '/data/www', [self::KEPT_DIR, self::GONE_DIR]);
        $this->process(DetectionType::DOCKER, self::GONE_DIR, $this->dockerData('gone-probe.example.com'));

        // E.g. replaying a dir result from before GONE_DIR was added.
        $this->process(DetectionType::DIRECTORY, '/data', [self::KEPT_DIR], new \DateTimeImmutable('-1 day'));

        $this->assertNotNull($this->findInstallation(self::GONE_DIR));
        $this->assertNotNull($this->entityManager->getRepository(Site::class)->findOneBy(['rootDir' => self::GONE_DIR]));
        $this->assertNotNull($this->entityManager->getRepository(DetectionResult::class)->findOneBy(['rootDir' => self::GONE_DIR]));
    }

    public function testKeepsOnlySitesInsideAListedDir(): void
    {
        $subDir = self::KEPT_DIR.'/web';
        $lookAlikes = [
            '/data/www/kept.probe/htdocs', // matched "kept_probe" when "_" was a LIKE wildcard
            self::KEPT_DIR.'_old',         // matched as a prefix without a "/" boundary
        ];

        $this->process(DetectionType::DIRECTORY, '/data/www', [self::KEPT_DIR], new \DateTimeImmutable('-1 day'));
        foreach ([$subDir, ...$lookAlikes] as $i => $rootDir) {
            $this->process(DetectionType::DOCKER, $rootDir, $this->dockerData('probe'.$i.'.example.com'));
        }

        // The state prod is in: look-alike sites moved onto the kept installation, their own installations gone.
        $siteRepository = $this->entityManager->getRepository(Site::class);
        $kept = $this->findInstallation(self::KEPT_DIR);
        foreach ($lookAlikes as $rootDir) {
            $site = $siteRepository->findOneBy(['rootDir' => $rootDir]);
            $this->entityManager->remove($site->getInstallation());
            $kept->addSite($site);
        }
        $this->entityManager->flush();

        $this->process(DetectionType::DIRECTORY, '/data', [self::KEPT_DIR]);

        $this->entityManager->clear();
        foreach ($lookAlikes as $rootDir) {
            $this->assertNull($siteRepository->findOneBy(['rootDir' => $rootDir]), $rootDir);
            $this->assertNull($this->entityManager->getRepository(DetectionResult::class)->findOneBy(['rootDir' => $rootDir]), $rootDir);
        }
        $this->assertSame(self::KEPT_DIR, $siteRepository->findOneBy(['rootDir' => $subDir])?->getInstallation()->getRootDir());
        $this->assertNotNull($this->entityManager->getRepository(DetectionResult::class)->findOneBy(['rootDir' => $subDir]));
    }

    /**
     * @return array<string, mixed>
     */
    private function dockerData(string $domain): array
    {
        return [
            'domain' => $domain,
            'containers' => [[
                'name' => 'phpfpm',
                'image' => 'itkdev/php8.4-fpm',
                'packages' => 'unknown',
                'audit' => 'unknown',
                'drupal' => 'unknown',
                'symfony' => 'unknown',
            ]],
        ];
    }

    /**
     * Persist a detection result and run it through the real processing flow.
     *
     * @param array<mixed> $data
     */
    private function process(string $type, string $rootDir, array $data, ?\DateTimeImmutable $lastContact = null): string
    {
        $server = $this->entityManager->getRepository(Server::class)->find($this->serverId);

        $detectionResult = new DetectionResult();
        $detectionResult->setType($type);
        $detectionResult->setRootDir($rootDir);
        $detectionResult->setServer($server);
        $detectionResult->setData(json_encode($data, JSON_THROW_ON_ERROR));
        $detectionResult->generateHash();
        $detectionResult->setLastContact($lastContact);
        $this->entityManager->persist($detectionResult);
        $this->entityManager->flush();

        $id = $detectionResult->getId();
        self::getContainer()->get(ProcessDetectionResultHandler::class)(new ProcessDetectionResult($id));

        return (string) $id;
    }

    private function findInstallation(string $rootDir): ?Installation
    {
        $this->entityManager->clear();

        return $this->entityManager->getRepository(Installation::class)->findOneBy(['rootDir' => $rootDir]);
    }
}
