<?php

namespace App\Service;

use App\Entity\GitTag;
use App\Entity\GitRepo;
use App\Entity\Installation;
use App\Repository\GitTagRepository;
use App\Repository\GitRepoRepository;
use App\Types\GitClonedByType;
use Doctrine\ORM\EntityManagerInterface;

class GitTagFactory
{
    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly GitTagRepository $gitTagRepository,
        private readonly GitRepoRepository $gitRepoRepository,
    ) {
    }

    public function setGitCloneData(Installation $installation, object $data): void
    {
        $oldTag = $installation->getGitTag();
        $tag = (isset($data->tag) && '' !== $data->tag) ? $data->tag : 'unknown';

        // @TODO handle more than one remote
        $remote = array_shift($data->remotes);

        $remoteParts = $this->parseRemoteUrl($remote);
        $provider = $remoteParts['host'];
        [$org, $repo] = explode('/', $remoteParts['path']);

        $gitRepo = $this->gitRepoRepository->findOneBy([
            'provider' => $provider,
            'organization' => $org,
            'repo' => $repo,
        ]);

        if (null === $gitRepo) {
            $gitRepo = new GitRepo();
            $gitRepo->setProvider($provider);
            $gitRepo->setOrganization($org);
            $gitRepo->setRepo($repo);
            $this->entityManager->persist($gitRepo);
        }

        $gitTag = $this->gitTagRepository->findOneBy([
            'repo' => $gitRepo,
            'tag' => $tag,
        ]);

        if (null === $gitTag) {
            $gitTag = new GitTag();
            $gitTag->addInstallation($installation);
            $gitTag->setTag($tag);

            $gitRepo->addGitTag($gitTag);

            $this->entityManager->persist($gitTag);
        }

        $installation->setGitClonedScheme($this->getClonedScheme($remote));
        if (isset($data->changes)) {
            $installation->setGitChanges(join("\n", $data->changes));
            $installation->setGitChangesCount(count($data->changes));
        } else {
            $installation->setGitChanges('');
            $installation->setGitChangesCount(0);
        }

        $this->cleanupGitTag($oldTag);
    }

    private function parseRemoteUrl(string $remote): array
    {
        $parts = [];
        $remote = strtolower($remote);

        if (str_ends_with($remote, '.git')) {
            $remote = substr($remote, 0, -4);
        }

        if (str_starts_with($remote, 'https')) {
            $parts = \parse_url($remote);
            // Strip leading slash
            $parts['path'] = substr($parts['path'], 1);
        }

        if (str_starts_with($remote, 'git@')) {
            $remote = substr($remote, 4);
            $split = explode(':', $remote);
            $parts['scheme'] = GitClonedByType::SSH;
            $parts['host'] = $split[0];
            $parts['path'] = $split[1];
        }

        return $parts;
    }

    private function getClonedScheme(string $remote): string
    {
        if (str_starts_with($remote, 'git@')) {
            return GitClonedByType::SSH;
        }

        if (str_starts_with($remote, 'https://')) {
            return GitClonedByType::HTTPS;
        }

        return GitClonedByType::UNKNOWN;
    }

    private function cleanupGitTag(?GitTag $gitTag): void
    {
        $installations = $gitTag?->getInstallations();
        $d=1;

        if (null !== $gitTag && $gitTag->getInstallations()->isEmpty()) {
            $gitRepo = $gitTag->getRepo();
            $gitRepo->removeGitTag($gitTag);
            $this->entityManager->remove($gitTag);

            if (empty($gitRepo->getGitTags())) {
                $this->entityManager->remove($gitRepo);
            }
        }
    }
}
