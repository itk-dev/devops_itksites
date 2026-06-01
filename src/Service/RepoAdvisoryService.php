<?php

declare(strict_types=1);

namespace App\Service;

use App\Controller\Admin\AdvisoryCrudController;
use App\Entity\CodeOwner;
use App\Repository\CodeOwnerRepository;
use App\Repository\GitRepoRepository;
use App\Repository\ProjectRepository;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Router\AdminUrlGenerator;

/**
 * Page-flow service backing the repo-advisories admin index.
 *
 * Owns the data assembly that used to live in RepoAdvisoryController: joining
 * GitRepo + Project + CodeOwner state, deriving installation type/version
 * labels, building the deep-link URL into AdvisoryCrudController, and
 * augmenting each row with live Leantime ticket state. Also orchestrates the
 * inline "create security ticket" action by combining a CodeOwner lookup with
 * LeantimeService calls.
 *
 * Kept separate from LeantimeService so the JSON-RPC client stays a thin
 * Leantime API wrapper, and separate from ServiceAgreementSyncService since
 * the shapes here exist only to feed the admin template.
 */
class RepoAdvisoryService
{
    public function __construct(
        private readonly AdminUrlGenerator $adminUrlGenerator,
        private readonly GitRepoRepository $gitRepoRepository,
        private readonly ProjectRepository $projectRepository,
        private readonly CodeOwnerRepository $codeOwnerRepository,
        private readonly LeantimeService $leantimeService,
    ) {
    }

    /**
     * Build the rows + warnings rendered by the repo-advisories admin index.
     *
     * Loads every GitRepo that has at least one advisory, attaches its
     * projects/codeowners, derives installation-type/version labels, computes
     * a filtered URL into AdvisoryCrudController for the repo's affected
     * package versions, and pairs each row with the most recent matching
     * Leantime "Sikkerhedsopdatering" ticket (if any).
     *
     * Failures while fetching Leantime tickets are caught and surfaced via the
     * returned `leantimeError` so the page can still render the rows without
     * ticket state — Leantime being down should not break the advisories view.
     *
     * @return array{rows: list<array<string, mixed>>, leantimeError: ?string} rows keyed by repo plus an optional Leantime error message for the caller to flash
     */
    public function buildIndexRows(): array
    {
        $reposWithCount = $this->gitRepoRepository->findReposWithAdvisoryCount();
        $packageVersionsPerRepo = $this->gitRepoRepository->findPackageVersionsPerRepoWithAdvisories();

        $ticketsByLeantimeId = [];
        $leantimeError = null;
        try {
            $ticketsByLeantimeId = $this->leantimeService->findOpenSecurityTickets();
        } catch (\Throwable $e) {
            $leantimeError = $e->getMessage();
        }

        $rows = [];
        foreach ($reposWithCount as $entry) {
            $rows[] = $this->buildRow($entry, $packageVersionsPerRepo, $ticketsByLeantimeId);
        }

        return [
            'rows' => $rows,
            'leantimeError' => $leantimeError,
        ];
    }

    /**
     * Create a Leantime "Sikkerhedsopdatering" ticket on behalf of a code owner.
     *
     * Resolves the code owner via the repository, asks LeantimeService to
     * translate its email into a Leantime user id, then creates the security
     * ticket on the given project. When the email cannot be mapped to a
     * Leantime user the ticket is still created — just unassigned — and the
     * caller learns about it via the `unassigned` flag in the result.
     *
     * @param string $codeOwnerId       RFC-4122 UUID of a CodeOwner entity
     * @param int    $leantimeProjectId numeric Leantime project id (external
     *                                  system's id, not an ORM id)
     *
     * @return array{ticketId: int, codeOwner: CodeOwner, unassigned: bool} the new ticket id, the resolved code owner, and whether the ticket is unassigned
     *
     * @throws \RuntimeException if the code owner cannot be found or the
     *                           Leantime API rejects the request
     */
    public function createSecurityTicketForCodeOwner(string $codeOwnerId, int $leantimeProjectId): array
    {
        $codeOwner = $this->codeOwnerRepository->find($codeOwnerId);
        if (!$codeOwner instanceof CodeOwner) {
            throw new \RuntimeException('Code owner not found.');
        }

        $userId = $this->leantimeService->findUserIdByEmail($codeOwner->getEmail());
        $ticketId = $this->leantimeService->createSecurityTicket($leantimeProjectId, $userId);

        return [
            'ticketId' => $ticketId,
            'codeOwner' => $codeOwner,
            'unassigned' => null === $userId,
        ];
    }

    /**
     * Build a single repo-advisory row for the admin index.
     *
     * Encapsulates the per-repo joins: projects, code owners (deduplicated by
     * id), installation type/version labels, the AdvisoryCrudController
     * deep-link, and the matching Leantime ticket (preferring the project
     * whose id has an open ticket; otherwise falling back to the first
     * project's Leantime id).
     *
     * @param array{repo: \App\Entity\GitRepo, advisoryCount: int}                  $entry                  repo + precomputed advisory count
     * @param array<string, list<string>>                                           $packageVersionsPerRepo map of repo-id → package version ids that have advisories
     * @param array<int, array{assigneeName: ?string, createdAt: ?string, id: int}> $ticketsByLeantimeId    open security tickets keyed by Leantime project id
     *
     * @return array<string, mixed> Row data ready for the Twig template
     */
    private function buildRow(array $entry, array $packageVersionsPerRepo, array $ticketsByLeantimeId): array
    {
        $repo = $entry['repo'];
        $projects = $this->projectRepository->findByGitRepo($repo);

        $codeOwners = [];
        foreach ($projects as $project) {
            foreach ($project->getCodeOwners() as $codeOwner) {
                $codeOwners[$codeOwner->getId()?->toRfc4122() ?? ''] = $codeOwner;
            }
        }

        $typesAndVersions = [];
        foreach ($repo->getGitTags() as $gitTag) {
            foreach ($gitTag->getInstallations() as $installation) {
                $key = trim(($installation->getType() ?? '').' '.($installation->getFrameworkVersion() ?? ''));
                if ('' !== $key) {
                    $typesAndVersions[$key] = true;
                }
            }
        }
        ksort($typesAndVersions);

        $repoKey = (string) $repo->getId();
        $packageVersionIds = $packageVersionsPerRepo[$repoKey] ?? [];
        $advisoriesUrl = [] === $packageVersionIds ? null : $this->adminUrlGenerator
            ->unsetAll()
            ->setController(AdvisoryCrudController::class)
            ->setAction(Crud::PAGE_INDEX)
            ->set('filters', ['packageVersions' => ['comparison' => '=', 'value' => $packageVersionIds]])
            ->generateUrl();

        $openTicket = null;
        $leantimeProjectId = null;
        foreach ($projects as $project) {
            $rawLeantimeId = $project->getLeantimeId();
            if (null === $rawLeantimeId || '' === $rawLeantimeId) {
                continue;
            }
            $candidateId = (int) $rawLeantimeId;
            if (null === $leantimeProjectId) {
                $leantimeProjectId = $candidateId;
            }
            if (isset($ticketsByLeantimeId[$candidateId])) {
                $openTicket = $ticketsByLeantimeId[$candidateId];
                $leantimeProjectId = $candidateId;
                break;
            }
        }

        return [
            'repo' => $repo,
            'advisoryCount' => $entry['advisoryCount'],
            'advisoriesUrl' => $advisoriesUrl,
            'typesAndVersions' => array_keys($typesAndVersions),
            'projects' => $projects,
            'codeOwners' => array_values($codeOwners),
            'openTicket' => $openTicket,
            'leantimeProjectId' => $leantimeProjectId,
        ];
    }
}
