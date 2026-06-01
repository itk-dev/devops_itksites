<?php

declare(strict_types=1);

namespace App\Controller\Admin;

use App\Entity\CodeOwner;
use App\Repository\CodeOwnerRepository;
use App\Repository\GitRepoRepository;
use App\Repository\ProjectRepository;
use App\Service\LeantimeService;
use App\Service\ServiceAgreementSyncService;
use EasyCorp\Bundle\EasyAdminBundle\Attribute\AdminRoute;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Router\AdminUrlGenerator;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Repo-advisories admin page.
 *
 * Not an EasyAdmin CRUD controller. The page joins four entities
 * (GitRepo + Project + SecurityContract + CodeOwner) with derived columns,
 * augments each row with live Leantime ticket state via LeantimeService, and
 * renders an inline `<select> + submit` form per row for the create-ticket
 * action. EA's Action API only emits link-style row actions, not in-row form
 * widgets, so a CRUD controller would need a custom index.html.twig override
 * that re-implements the same row loops anyway.
 *
 * The actions still integrate with the admin shell: routes use #[AdminRoute],
 * so EA auto-tags this class as an admin-route controller, populates
 * AdminContext, and the templates extend the EasyAdmin layout normally.
 */
class RepoAdvisoryController extends AbstractController
{
    private const string CSRF_INTENT = 'repo_advisory_action';

    public function __construct(
        private readonly AdminUrlGenerator $adminUrlGenerator,
        private readonly GitRepoRepository $gitRepoRepository,
        private readonly ProjectRepository $projectRepository,
        private readonly CodeOwnerRepository $codeOwnerRepository,
        private readonly ServiceAgreementSyncService $serviceAgreementSyncService,
        private readonly LeantimeService $leantimeService,
    ) {
    }

    #[AdminRoute(path: '/repo-advisories', name: 'repo_advisories', options: ['methods' => ['GET']])]
    public function index(): Response
    {
        $reposWithCount = $this->gitRepoRepository->findReposWithAdvisoryCount();
        $packageVersionsPerRepo = $this->gitRepoRepository->findPackageVersionsPerRepoWithAdvisories();

        $ticketsByLeantimeId = [];
        try {
            $ticketsByLeantimeId = $this->leantimeService->findOpenSecurityTickets();
        } catch (\Throwable $e) {
            $this->addFlash('warning', sprintf('Could not fetch Leantime tickets: %s', $e->getMessage()));
        }

        $rows = [];
        foreach ($reposWithCount as $entry) {
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

            $rows[] = [
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

        return $this->render('admin/repo_advisory/index.html.twig', [
            'rows' => $rows,
            'csrf_intent' => self::CSRF_INTENT,
        ]);
    }

    #[AdminRoute(path: '/repo-advisories/sync', name: 'repo_advisories_sync', options: ['methods' => ['POST']])]
    public function sync(Request $request): RedirectResponse
    {
        if (!$this->isCsrfTokenValid(self::CSRF_INTENT, (string) $request->request->get('_token'))) {
            $this->addFlash('error', 'Invalid CSRF token; please retry.');

            return $this->redirectToRoute('admin_repo_advisories');
        }

        try {
            $result = $this->serviceAgreementSyncService->syncAll();

            $this->addFlash('info', sprintf('Synced %d projects.', $result['projects']));

            if (!empty($result['unmatchedRepoNames'])) {
                $this->addFlash('warning', sprintf(
                    'Could not link %d GitHub repo name(s) to existing GitRepo entries: %s',
                    count($result['unmatchedRepoNames']),
                    implode(', ', $result['unmatchedRepoNames']),
                ));
            }
        } catch (\Throwable $e) {
            $this->addFlash('error', sprintf('An error occurred while syncing: %s', $e->getMessage()));
        }

        return $this->redirectToRoute('admin_repo_advisories');
    }

    #[AdminRoute(path: '/repo-advisories/{repoId}/create-ticket', name: 'repo_advisories_create_ticket', options: ['methods' => ['POST']])]
    public function createTicket(Request $request, string $repoId): RedirectResponse
    {
        unset($repoId); // route param kept for future per-repo context

        if (!$this->isCsrfTokenValid(self::CSRF_INTENT, (string) $request->request->get('_token'))) {
            $this->addFlash('error', 'Invalid CSRF token; please retry.');

            return $this->redirectToRoute('admin_repo_advisories');
        }

        $codeOwnerId = (string) $request->request->get('codeOwnerId', '');
        $leantimeProjectId = (int) $request->request->get('leantimeProjectId', 0);

        if ('' === $codeOwnerId) {
            $this->addFlash('warning', 'No code owner selected.');

            return $this->redirectToRoute('admin_repo_advisories');
        }
        if (0 === $leantimeProjectId) {
            $this->addFlash('warning', 'No Leantime project linked to this repo.');

            return $this->redirectToRoute('admin_repo_advisories');
        }

        $codeOwner = $this->codeOwnerRepository->find($codeOwnerId);
        if (!$codeOwner instanceof CodeOwner) {
            $this->addFlash('error', 'Code owner not found.');

            return $this->redirectToRoute('admin_repo_advisories');
        }

        try {
            $userId = $this->leantimeService->findUserIdByEmail($codeOwner->getEmail());
            if (null === $userId) {
                $this->addFlash('warning', sprintf(
                    'Code owner %s has no matching Leantime user (email: %s); creating ticket unassigned.',
                    $codeOwner->getName(),
                    $codeOwner->getEmail(),
                ));
            }

            $ticketId = $this->leantimeService->createSecurityTicket($leantimeProjectId, $userId);
            $this->addFlash('info', sprintf('Created Leantime ticket #%d.', $ticketId));
        } catch (\Throwable $e) {
            $this->addFlash('error', sprintf('Failed to create Leantime ticket: %s', $e->getMessage()));
        }

        return $this->redirectToRoute('admin_repo_advisories');
    }
}
