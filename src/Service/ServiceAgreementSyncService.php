<?php

namespace App\Service;

use App\Entity\CodeOwner;
use App\Entity\GitRepo;
use App\Entity\Project;
use App\Entity\SecurityContract;
use App\Repository\CodeOwnerRepository;
use App\Repository\GitRepoRepository;
use App\Repository\ProjectRepository;
use App\Repository\SecurityContractRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Contracts\HttpClient\Exception\ExceptionInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;

/**
 * Syncs projects (with nested service agreements and codeowners) from the
 * Economics API (economics.itkdev.dk) into local Project / CodeOwner /
 * SecurityContract entities.
 */
readonly class ServiceAgreementSyncService
{
    private const string ENDPOINT = '/api/projects';
    private const string DEFAULT_TIMEZONE = 'UTC';

    public function __construct(
        private EntityManagerInterface $entityManager,
        private HttpClientInterface $economicsClient,
        private ProjectRepository $projectRepository,
        private CodeOwnerRepository $codeOwnerRepository,
        private SecurityContractRepository $securityContractRepository,
        private GitRepoRepository $gitRepoRepository,
    ) {
    }

    /**
     * Fetch all projects from the Economics API and sync them locally.
     *
     * @return array{projects:int, unmatchedRepoNames:list<string>}
     *
     * @throws \RuntimeException|\Exception if the API request fails
     */
    public function syncAll(): array
    {
        try {
            $response = $this->economicsClient->request('GET', self::ENDPOINT);
            $projectsData = $response->toArray();
        } catch (ExceptionInterface $e) {
            throw new \RuntimeException('Failed to fetch projects from Economics API: '.$e->getMessage(), 0, $e);
        }

        $existingProjects = [];
        foreach ($this->projectRepository->findAll() as $project) {
            $existingProjects[$project->getEconomicsId()] = $project;
        }

        $existingCodeOwners = [];
        foreach ($this->codeOwnerRepository->findAll() as $codeOwner) {
            $existingCodeOwners[$codeOwner->getEconomicsId()] = $codeOwner;
        }

        $existingContracts = [];
        foreach ($this->securityContractRepository->findAll() as $contract) {
            $existingContracts[$contract->getEconomicsId()] = $contract;
        }

        $existingGitReposByRepo = [];
        foreach ($this->gitRepoRepository->findAll() as $repo) {
            $existingGitReposByRepo[$repo->getRepo()] = $repo;
        }

        $seenProjectIds = [];
        $seenCodeOwnerIds = [];
        $seenContractIds = [];
        $unmatchedRepoNames = [];

        foreach ($projectsData as $data) {
            $project = $existingProjects[$data['id']] ?? new Project();
            $project->setEconomicsId($data['id']);
            $project->setName($data['name'] ?? '');
            $project->setLeantimeId(isset($data['projectTrackerId']) ? (string) $data['projectTrackerId'] : null);
            $project->setLeantimeUrl($data['leantimeUrl'] ?? null);

            $this->syncCodeOwners($project, $data['codeowners'] ?? [], $existingCodeOwners, $seenCodeOwnerIds);
            $this->syncGitRepos($project, $data['githubRepos'] ?? null, $existingGitReposByRepo, $unmatchedRepoNames);

            $this->entityManager->persist($project);
            $existingProjects[$data['id']] = $project;
            $seenProjectIds[] = $data['id'];

            $serviceAgreementData = $data['serviceAgreement'] ?? null;
            if (is_array($serviceAgreementData) && isset($serviceAgreementData['id'])) {
                $contract = $existingContracts[$serviceAgreementData['id']] ?? new SecurityContract();
                $this->mapServiceAgreementToContract($contract, $serviceAgreementData, $project);
                $this->entityManager->persist($contract);
                $existingContracts[$serviceAgreementData['id']] = $contract;
                $seenContractIds[] = $serviceAgreementData['id'];
            }
        }

        foreach ($existingContracts as $economicsId => $contract) {
            if (!in_array($economicsId, $seenContractIds, true)) {
                $this->entityManager->remove($contract);
            }
        }

        foreach ($existingProjects as $economicsId => $project) {
            if (!in_array($economicsId, $seenProjectIds, true)) {
                $this->entityManager->remove($project);
            }
        }

        foreach ($existingCodeOwners as $economicsId => $codeOwner) {
            if (!in_array($economicsId, $seenCodeOwnerIds, true)) {
                $this->entityManager->remove($codeOwner);
            }
        }

        $this->entityManager->flush();

        return [
            'projects' => count($projectsData),
            'unmatchedRepoNames' => array_keys($unmatchedRepoNames),
        ];
    }

    /**
     * @param array<int, array<string, mixed>> $codeOwnersData
     * @param array<int, CodeOwner>            $existingCodeOwners passed by reference so newly-created owners are reused within one sync run
     * @param list<int>                        $seenCodeOwnerIds
     *
     * @param-out array<int, CodeOwner> $existingCodeOwners
     * @param-out list<int>             $seenCodeOwnerIds
     */
    private function syncCodeOwners(Project $project, array $codeOwnersData, array &$existingCodeOwners, array &$seenCodeOwnerIds): void
    {
        $desired = [];
        foreach ($codeOwnersData as $ownerData) {
            if (!isset($ownerData['id'])) {
                continue;
            }

            $economicsId = (int) $ownerData['id'];
            $owner = $existingCodeOwners[$economicsId] ?? new CodeOwner();
            $owner->setEconomicsId($economicsId);
            $owner->setName((string) ($ownerData['name'] ?? ''));
            $owner->setEmail((string) ($ownerData['email'] ?? ''));

            $existingCodeOwners[$economicsId] = $owner;
            $seenCodeOwnerIds[] = $economicsId;

            $this->entityManager->persist($owner);
            $desired[$economicsId] = $owner;
        }

        foreach ($project->getCodeOwners() as $existing) {
            if (!isset($desired[$existing->getEconomicsId()])) {
                $project->removeCodeOwner($existing);
            }
        }
        foreach ($desired as $owner) {
            $project->addCodeOwner($owner);
        }
    }

    /**
     * @param array<string, GitRepo> $existingGitReposByRepo
     * @param array<string, true>    $unmatchedRepoNames     accumulator across all projects
     */
    private function syncGitRepos(Project $project, ?string $githubReposString, array $existingGitReposByRepo, array &$unmatchedRepoNames): void
    {
        $names = [];
        if (null !== $githubReposString && '' !== $githubReposString) {
            foreach (preg_split('/\r\n|\r|\n/', $githubReposString) ?: [] as $line) {
                $trimmed = trim($line);
                if ('' !== $trimmed) {
                    $names[] = $trimmed;
                }
            }
        }

        $desired = [];
        foreach ($names as $repoName) {
            if (isset($existingGitReposByRepo[$repoName])) {
                $repo = $existingGitReposByRepo[$repoName];
                $desired[spl_object_id($repo)] = $repo;
            } else {
                $unmatchedRepoNames[$repoName] = true;
            }
        }

        foreach ($project->getGitRepos() as $existing) {
            if (!isset($desired[spl_object_id($existing)])) {
                $project->removeGitRepo($existing);
            }
        }
        foreach ($desired as $repo) {
            $project->addGitRepo($repo);
        }
    }

    /**
     * @param array<string, mixed> $data
     *
     * @throws \Exception
     */
    private function mapServiceAgreementToContract(SecurityContract $contract, array $data, Project $project): void
    {
        $contract->setEconomicsId($data['id']);
        $contract->setProject($project);
        $contract->setHostingProvider($data['hostingProvider'] ?? null);
        $contract->setDocumentUrl($data['documentUrl'] ?? null);
        $contract->setMonthlyPrice(isset($data['price']) ? (float) $data['price'] : null);
        $contract->setValidFrom($this->parseDate($data['validFrom'] ?? null));
        $contract->setValidTo($this->parseDate($data['validTo'] ?? null));
        $contract->setActive($data['isActive'] ?? false);
        $contract->setEol($data['isEol'] ?? false);
        $contract->setClientContactName($data['clientContactName'] ?? null);
        $contract->setClientContactEmail($data['clientContactEmail'] ?? null);
        $contract->setDedicatedServer($data['dedicatedServer'] ?? false);
        $contract->setServerSize($data['serverSize'] ?? null);
        $contract->setSystemOwnerNotices($data['systemOwnerNotices'] ?? null);
    }

    /**
     * Parse the Economics API date format ({date, timezone_type, timezone}) into a DateTimeImmutable.
     *
     * @param array{date?: string, timezone_type?: int, timezone?: string}|null $dateData
     *
     * @throws \Exception if the date string cannot be parsed
     */
    private function parseDate(?array $dateData): ?\DateTimeImmutable
    {
        if (null === $dateData || !isset($dateData['date'])) {
            return null;
        }

        $timezone = new \DateTimeZone($dateData['timezone'] ?? self::DEFAULT_TIMEZONE);

        return new \DateTimeImmutable($dateData['date'], $timezone);
    }
}
