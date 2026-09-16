<?php

declare(strict_types=1);

namespace App\Tests\Integration;

use App\Entity\DeployResult;
use App\Entity\DetectionResult;
use App\Entity\GitRepo;
use App\Entity\Installation;
use App\Entity\Server;
use App\Handler\GitHandler;
use App\MessageHandler\DeployResultHandler;
use App\Types\CodeSourceType;
use App\Types\DetectionType;
use Doctrine\ORM\EntityManagerInterface;
use Hautelook\AliceBundle\PhpUnit\RefreshDatabaseTrait;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

/**
 * Deploy reporting against a real database.
 *
 * The unit tests cover the decisions; this covers the thing they cannot see -
 * that the rows a deployment creates still exist after the harvester has been
 * over the same installation, given RemovedRelationsListener deletes orphaned
 * git tags and repositories on every flush.
 */
class DeployReportingTest extends KernelTestCase
{
    use RefreshDatabaseTrait;

    private const string ROOT_DIR = '/data/www/deployed-example/htdocs';
    private const string REPO_URL = 'https://github.com/itk-dev/deployed-example.git';

    private EntityManagerInterface $entityManager;
    private Server $server;

    protected function setUp(): void
    {
        parent::setUp();

        self::bootKernel();

        $this->entityManager = self::getContainer()->get(EntityManagerInterface::class);
        $this->server = $this->entityManager->getRepository(Server::class)->findAll()[0];

        $this->givenAnInstallationExists();
    }

    public function testADeploymentRecordsItsRepositoryAndTag(): void
    {
        $this->reportDeploy('1.4.2');

        $installation = $this->reloadInstallation();

        $this->assertSame('1.4.2', $installation->getGitTag()?->getTag());
        $this->assertSame(CodeSourceType::ARTIFACT, $installation->getCodeSource());
        $this->assertNotNull($this->findGitRepo(), 'The deployment should create the repository it deployed');
    }

    /**
     * An artifact deployment leaves no .git directory, so the harvester reports
     * an empty git result for it on every scan. That must not take the deployed
     * tag - or its repository - away with it.
     */
    public function testAHarvesterScanDoesNotWipeADeployedTag(): void
    {
        $this->reportDeploy('1.4.2');

        $this->whenTheHarvesterFindsNoCheckout();

        $installation = $this->reloadInstallation();

        $this->assertSame('1.4.2', $installation->getGitTag()?->getTag(), 'The deployed tag should survive a harvester scan');
        $this->assertNotNull($this->findGitRepo(), 'The repository row should survive a harvester scan');
    }

    /**
     * The guard is specific to artifact installations: a checkout that really
     * has gone away must still be cleared.
     */
    public function testAHarvesterScanStillClearsACheckoutTag(): void
    {
        $this->reportDeploy('1.4.2');

        $installation = $this->reloadInstallation();
        $installation->setCodeSource(CodeSourceType::GIT);
        $this->entityManager->flush();

        $this->whenTheHarvesterFindsNoCheckout();

        $this->assertNull($this->reloadInstallation()->getGitTag());
    }

    private function givenAnInstallationExists(): void
    {
        $detectionResult = $this->detectionResult(DetectionType::DIRECTORY, '[]');

        $installation = new Installation();
        $installation->setDetectionResult($detectionResult);
        $installation->setRootDir(self::ROOT_DIR);

        $this->entityManager->persist($installation);
        $this->entityManager->flush();
    }

    private function reportDeploy(string $tag): void
    {
        $deployResult = new DeployResult();
        $deployResult->setServerName($this->server->getName());
        $deployResult->setRootDir(self::ROOT_DIR);
        $deployResult->setRepoUrl(self::REPO_URL);
        $deployResult->setTag($tag);

        self::getContainer()->get(DeployResultHandler::class)->__invoke($deployResult);
    }

    private function whenTheHarvesterFindsNoCheckout(): void
    {
        // An empty payload is what the git detection sends for a directory that
        // is not a git checkout.
        self::getContainer()->get(GitHandler::class)->handleResult(
            $this->detectionResult(DetectionType::GIT, '')
        );

        $this->entityManager->flush();
    }

    private function detectionResult(string $type, string $data): DetectionResult
    {
        $detectionResult = new DetectionResult();
        $detectionResult->setType($type);
        $detectionResult->setRootDir(self::ROOT_DIR);
        $detectionResult->setData($data);
        $detectionResult->setServer($this->entityManager->find(Server::class, $this->server->getId()));
        $detectionResult->setLastContact();
        $detectionResult->generateHash();

        $this->entityManager->persist($detectionResult);
        $this->entityManager->flush();

        return $detectionResult;
    }

    private function reloadInstallation(): Installation
    {
        $this->entityManager->clear();

        return $this->entityManager->getRepository(Installation::class)->findOneBy([
            'rootDir' => self::ROOT_DIR,
        ]);
    }

    private function findGitRepo(): ?GitRepo
    {
        return $this->entityManager->getRepository(GitRepo::class)->findOneBy([
            'provider' => 'github.com',
            'organization' => 'itk-dev',
            'repo' => 'deployed-example',
        ]);
    }
}
