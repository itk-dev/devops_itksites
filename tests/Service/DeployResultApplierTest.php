<?php

declare(strict_types=1);

namespace App\Tests\Service;

use App\Entity\DeployResult;
use App\Entity\GitRepo;
use App\Entity\GitTag;
use App\Entity\Installation;
use App\Entity\Server;
use App\Repository\InstallationRepository;
use App\Service\DeployResultApplier;
use App\Service\GitTagFactory;
use App\Types\CodeSourceType;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Uid\Ulid;

class DeployResultApplierTest extends TestCase
{
    private InstallationRepository $installationRepositoryStub;
    private GitTagFactory $gitTagFactoryStub;

    private DeployResultApplier $applier;

    protected function setUp(): void
    {
        parent::setUp();

        $this->installationRepositoryStub = $this->createStub(InstallationRepository::class);
        $this->gitTagFactoryStub = $this->createStub(GitTagFactory::class);

        $this->applier = new DeployResultApplier(
            $this->installationRepositoryStub,
            $this->gitTagFactoryStub,
        );
    }

    public function testItLinksTheTagAndMarksTheInstallationAsAnArtifact(): void
    {
        $installation = new Installation();
        $installation->setGitChanges('M  src/Kernel.php');
        $installation->setGitChangesCount(1);

        $this->installationRepositoryStub->method('findOneBy')->willReturn($installation);
        $this->gitTagFactoryStub->method('resolveGitTag')->willReturn($this->gitTag());

        $this->applier->apply($this->deployResult());

        $this->assertSame('1.4.2', $installation->getGitTag()?->getTag());
        $this->assertSame(CodeSourceType::ARTIFACT, $installation->getCodeSource());

        // A deployment has no working copy, so it must not make claims about one.
        $this->assertSame('M  src/Kernel.php', $installation->getGitChanges());
        $this->assertSame(1, $installation->getGitChangesCount());
    }

    /**
     * An installation cannot be created from a deploy result, so a deployment
     * the harvester has not scanned yet is simply not applied.
     */
    public function testItDoesNothingWhenNoInstallationMatches(): void
    {
        $this->installationRepositoryStub->method('findOneBy')->willReturn(null);

        $this->expectNotToPerformAssertions();

        $this->applier->apply($this->deployResult());
    }

    public function testItDoesNothingWhenTheRemoteCannotBeResolved(): void
    {
        $installation = new Installation();

        $this->installationRepositoryStub->method('findOneBy')->willReturn($installation);
        $this->gitTagFactoryStub->method('resolveGitTag')->willReturn(null);

        $this->applier->apply($this->deployResult());

        $this->assertNull($installation->getGitTag());
        $this->assertNull($installation->getCodeSource());
    }

    private function gitTag(): GitTag
    {
        $gitRepo = new GitRepo();
        $gitRepo->setProvider('github.com')->setOrganization('itk-dev')->setRepo('example');

        $gitTag = new GitTag();
        $gitTag->setTag('1.4.2');
        $gitRepo->addGitTag($gitTag);

        return $gitTag;
    }

    private function deployResult(): DeployResult
    {
        $server = new Server();
        $server->setId(new Ulid());

        $deployResult = new DeployResult();
        $deployResult->setServerName('srvitkphp84.itkdev.dk');
        $deployResult->setRootDir('/data/www/example/htdocs');
        $deployResult->setRepoUrl('https://github.com/itk-dev/example.git');
        $deployResult->setTag('1.4.2');
        $deployResult->setServer($server);

        return $deployResult;
    }
}
