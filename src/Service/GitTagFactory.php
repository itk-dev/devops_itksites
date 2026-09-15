<?php

declare(strict_types=1);

namespace App\Service;

use App\Entity\GitRepo;
use App\Entity\GitTag;
use App\Entity\Installation;
use App\Repository\GitRepoRepository;
use App\Repository\GitTagRepository;
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

        $criteria = self::parseRemote($remote);
        if (null === $criteria) {
            return;
        }

        $gitRepo = $this->gitRepoRepository->findOneBy($criteria);

        if (null === $gitRepo) {
            $gitRepo = new GitRepo();
            $this->entityManager->persist($gitRepo);

            $gitRepo->setProvider($criteria['provider']);
            $gitRepo->setOrganization($criteria['organization']);
            $gitRepo->setRepo($criteria['repo']);
        }

        $gitTag = $this->gitTagRepository->findOneBy([
            'repo' => $gitRepo,
            'tag' => $tag,
        ]);

        if (null === $gitTag) {
            $gitTag = new GitTag();
            $this->entityManager->persist($gitTag);

            $gitTag->addInstallation($installation);
            $gitTag->setTag($tag);

            $gitRepo->addGitTag($gitTag);
        }

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
     * Split a git remote into GitRepo lookup criteria.
     *
     * Handles both forms a remote arrives in — `https://github.com/aakb/dokk1gh.git`
     * from the harvester and `git@github.com:aakb/dokk1gh.git` from an SSH
     * clone — plus the scheme-less `github.com/aakb/dokk1gh` that turns up in
     * hand-typed Economics data. Everything is lowercased so the same repo
     * resolves to one row whichever side registered it first, which is why
     * every caller matching a GitRepo has to come through here.
     *
     * @param string $remote raw remote URL
     *
     * @return array{provider: string, organization: string, repo: string}|null criteria for findOneBy(), or null when the remote names no `organization/repo`
     */
    public static function parseRemote(string $remote): ?array
    {
        $remote = strtolower(trim($remote));

        if (str_ends_with($remote, '.git')) {
            $remote = substr($remote, 0, -4);
        }

        if (str_starts_with($remote, 'git@')) {
            [$host, $path] = array_pad(explode(':', substr($remote, 4), 2), 2, '');
        } else {
            $parts = parse_url(str_contains($remote, '://') ? $remote : 'https://'.$remote) ?: [];
            $host = $parts['host'] ?? '';
            $path = $parts['path'] ?? '';
        }

        $segments = explode('/', trim($path, '/'));

        if ('' === $host || 2 !== count($segments) || '' === $segments[0] || '' === $segments[1]) {
            return null;
        }

        return [
            'provider' => $host,
            'organization' => $segments[0],
            'repo' => $segments[1],
        ];
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
