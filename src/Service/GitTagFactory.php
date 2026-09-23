<?php

declare(strict_types=1);

namespace App\Service;

use App\Entity\GitRepo;
use App\Entity\GitTag;
use App\Entity\Installation;
use App\Repository\GitRepoRepository;
use App\Repository\GitTagRepository;
use App\Types\CodeSourceType;
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
        $tag = (isset($data->tag) && '' !== $data->tag) ? $data->tag : 'unknown';

        // @TODO handle more than one remote
        $remote = array_shift($data->remotes);

        $gitTag = $this->resolveGitTag($remote, $tag);

        if (null === $gitTag) {
            return;
        }

        // Linked outside resolveGitTag(): the installation must be attached to
        // the tag whether or not the tag row was created just now. When the tag
        // already exists - a redeployment, or the same release on a second
        // server - the installation would otherwise keep pointing at its
        // previous tag, or at nothing at all.
        $gitTag->addInstallation($installation);

        $installation->setCodeSource(CodeSourceType::GIT);
        $installation->setGitClonedScheme($this->getClonedScheme($remote));
        if (isset($data->changes)) {
            $installation->setGitChanges(join("\n", $data->changes));
            $installation->setGitChangesCount(count($data->changes));
        } else {
            $installation->setGitChanges('');
            $installation->setGitChangesCount(0);
        }
    }

    /**
     * Find or create the repository and tag a remote points at.
     *
     * Shared with deployments, which know a repository and a tag but have no
     * working copy to report anything else about.
     *
     * Returns null when the remote cannot be parsed, rather than failing: a
     * remote is external input, and one unreadable value should not take down
     * the processing of everything else.
     */
    public function resolveGitTag(string $remote, string $tag): ?GitTag
    {
        $remoteParts = $this->parseRemoteUrl($remote);

        if (!isset($remoteParts['host'], $remoteParts['path'])) {
            return null;
        }

        $path = explode('/', (string) $remoteParts['path']);

        if (2 !== count($path)) {
            return null;
        }

        [$org, $repo] = $path;
        $provider = $remoteParts['host'];

        $gitRepo = $this->gitRepoRepository->findOneBy([
            'provider' => $provider,
            'organization' => $org,
            'repo' => $repo,
        ]);

        if (null === $gitRepo) {
            $gitRepo = new GitRepo();
            $this->entityManager->persist($gitRepo);

            $gitRepo->setProvider($provider);
            $gitRepo->setOrganization($org);
            $gitRepo->setRepo($repo);
        }

        $gitTag = $this->gitTagRepository->findOneBy([
            'repo' => $gitRepo,
            'tag' => $tag,
        ]);

        if (null === $gitTag) {
            $gitTag = new GitTag();
            $this->entityManager->persist($gitTag);

            $gitTag->setTag($tag);

            $gitRepo->addGitTag($gitTag);
        }

        return $gitTag;
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
}
