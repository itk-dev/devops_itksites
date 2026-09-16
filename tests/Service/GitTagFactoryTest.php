<?php

declare(strict_types=1);

namespace App\Tests\Service;

use App\Entity\GitRepo;
use App\Entity\GitTag;
use App\Entity\Installation;
use App\Repository\GitRepoRepository;
use App\Repository\GitTagRepository;
use App\Service\GitTagFactory;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\TestCase;

class GitTagFactoryTest extends TestCase
{
    private EntityManagerInterface $entityManagerMock;
    private GitTagRepository $gitTagRepositoryStub;
    private GitRepoRepository $gitRepoRepositoryStub;

    private GitTagFactory $factory;

    protected function setUp(): void
    {
        parent::setUp();

        $this->entityManagerMock = $this->createMock(EntityManagerInterface::class);
        $this->gitTagRepositoryStub = $this->createStub(GitTagRepository::class);
        $this->gitRepoRepositoryStub = $this->createStub(GitRepoRepository::class);

        $this->factory = new GitTagFactory(
            $this->entityManagerMock,
            $this->gitTagRepositoryStub,
            $this->gitRepoRepositoryStub,
        );
    }

    public function testUnknownTagIsCreatedAndLinked(): void
    {
        $this->gitRepoRepositoryStub->method('findOneBy')->willReturn(null);
        $this->gitTagRepositoryStub->method('findOneBy')->willReturn(null);

        // Both the repo and the tag are new, so both are persisted.
        $this->entityManagerMock->expects($this->exactly(2))->method('persist');

        $installation = new Installation();

        $this->factory->setGitCloneData($installation, $this->data('1.4.2'));

        $gitTag = $installation->getGitTag();
        $this->assertNotNull($gitTag, 'A newly created tag should be linked to the installation');
        $this->assertSame('1.4.2', $gitTag->getTag());
        $this->assertSame('github.com', $gitTag->getRepo()->getProvider());
        $this->assertSame('itk-dev', $gitTag->getRepo()->getOrganization());
        $this->assertSame('example', $gitTag->getRepo()->getRepo());
    }

    /**
     * A tag row already exists once the same release has been seen before - a
     * redeployment, or the same release on a second server. The installation
     * must still be linked to it.
     */
    public function testExistingTagIsLinkedToAFurtherInstallation(): void
    {
        $gitRepo = new GitRepo();
        $gitRepo->setProvider('github.com')->setOrganization('itk-dev')->setRepo('example');

        $existingTag = new GitTag();
        $existingTag->setTag('1.4.2');
        $gitRepo->addGitTag($existingTag);

        $firstInstallation = new Installation();
        $existingTag->addInstallation($firstInstallation);

        $this->gitRepoRepositoryStub->method('findOneBy')->willReturn($gitRepo);
        $this->gitTagRepositoryStub->method('findOneBy')->willReturn($existingTag);

        // Nothing new is persisted when both the repo and the tag already exist.
        $this->entityManagerMock->expects($this->never())->method('persist');

        $secondInstallation = new Installation();

        $this->factory->setGitCloneData($secondInstallation, $this->data('1.4.2'));

        $this->assertSame($existingTag, $secondInstallation->getGitTag());
        $this->assertSame($existingTag, $firstInstallation->getGitTag(), 'The first installation keeps its link');
        $this->assertCount(2, $existingTag->getInstallations());
    }

    /**
     * Build the decoded payload the git detection sends.
     */
    private function data(string $tag): object
    {
        return (object) [
            'remotes' => ['https://github.com/itk-dev/example.git'],
            'tag' => $tag,
            'changes' => [],
        ];
    }
}
