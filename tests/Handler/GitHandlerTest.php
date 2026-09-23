<?php

declare(strict_types=1);

namespace App\Tests\Handler;

use App\Entity\DetectionResult;
use App\Entity\GitRepo;
use App\Entity\GitTag;
use App\Entity\Installation;
use App\Handler\GitHandler;
use App\Service\GitTagFactory;
use App\Service\InstallationFactory;
use App\Types\CodeSourceType;
use App\Types\DetectionType;
use PHPUnit\Framework\TestCase;

class GitHandlerTest extends TestCase
{
    private InstallationFactory $installationFactoryStub;
    private GitTagFactory $gitTagFactoryStub;

    private GitHandler $handler;

    protected function setUp(): void
    {
        parent::setUp();

        $this->installationFactoryStub = $this->createStub(InstallationFactory::class);
        $this->gitTagFactoryStub = $this->createStub(GitTagFactory::class);

        $this->handler = new GitHandler(
            $this->installationFactoryStub,
            $this->gitTagFactoryStub,
        );
    }

    /**
     * The harvester sends an empty result for a directory that is not a git
     * checkout, which is how we learn a checkout has gone away.
     */
    public function testEmptyResultClearsTheTagOfACheckout(): void
    {
        $installation = $this->installationWithTag(CodeSourceType::GIT);
        $this->installationFactoryStub->method('getInstallation')->willReturn($installation);

        $this->handler->handleResult($this->emptyGitResult());

        $this->assertNull($installation->getGitTag());
    }

    /**
     * An artifact deployment never has a .git directory, so the harvester always
     * reports nothing for it. Clearing the tag here would discard what the
     * deployment told us - and RemovedRelationsListener would then delete the
     * tag and its repository outright.
     */
    public function testEmptyResultKeepsTheTagOfAnArtifactInstallation(): void
    {
        $installation = $this->installationWithTag(CodeSourceType::ARTIFACT);
        $gitTag = $installation->getGitTag();
        $this->installationFactoryStub->method('getInstallation')->willReturn($installation);

        $this->handler->handleResult($this->emptyGitResult());

        $this->assertSame($gitTag, $installation->getGitTag());
    }

    public function testSupportsGitOnly(): void
    {
        $this->assertTrue($this->handler->supportsType(DetectionType::GIT));
        $this->assertFalse($this->handler->supportsType(DetectionType::DOCKER));
    }

    private function installationWithTag(string $codeSource): Installation
    {
        $gitRepo = new GitRepo();
        $gitRepo->setProvider('github.com')->setOrganization('itk-dev')->setRepo('example');

        $gitTag = new GitTag();
        $gitTag->setTag('1.4.2');
        $gitRepo->addGitTag($gitTag);

        $installation = new Installation();
        $gitTag->addInstallation($installation);
        $installation->setCodeSource($codeSource);

        return $installation;
    }

    /**
     * What the harvester posts for a directory with no .git in it.
     */
    private function emptyGitResult(): DetectionResult
    {
        $detectionResult = new DetectionResult();
        $detectionResult->setType(DetectionType::GIT);
        $detectionResult->setRootDir('/data/www/example/htdocs');
        $detectionResult->setData('');

        return $detectionResult;
    }
}
