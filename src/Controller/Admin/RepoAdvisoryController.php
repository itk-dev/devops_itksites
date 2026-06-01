<?php

declare(strict_types=1);

namespace App\Controller\Admin;

use App\Service\RepoAdvisoryService;
use App\Service\ServiceAgreementSyncService;
use EasyCorp\Bundle\EasyAdminBundle\Attribute\AdminRoute;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Repo-advisories admin page.
 *
 * Not an EasyAdmin CRUD controller. The page joins four entities
 * (GitRepo + Project + SecurityContract + CodeOwner) with derived columns,
 * augments each row with live Leantime ticket state, and renders an inline
 * `<select> + submit` form per row for the create-ticket action. EA's Action
 * API only emits link-style row actions, not in-row form widgets, so a CRUD
 * controller would need a custom index.html.twig override that re-implements
 * the same row loops anyway.
 *
 * Data assembly and Leantime orchestration live in RepoAdvisoryService — this
 * controller only handles request parsing, CSRF, flashes, and rendering.
 *
 * The actions still integrate with the admin shell: routes use #[AdminRoute],
 * so EA auto-tags this class as an admin-route controller, populates
 * AdminContext, and the templates extend the EasyAdmin layout normally.
 */
class RepoAdvisoryController extends AbstractController
{
    private const string CSRF_INTENT = 'repo_advisory_action';

    public function __construct(
        private readonly RepoAdvisoryService $repoAdvisoryService,
        private readonly ServiceAgreementSyncService $serviceAgreementSyncService,
    ) {
    }

    #[AdminRoute(path: '/repo-advisories', name: 'repo_advisories', options: ['methods' => ['GET']])]
    public function index(): Response
    {
        $result = $this->repoAdvisoryService->buildIndexRows();

        if (null !== $result['leantimeError']) {
            $this->addFlash('warning', sprintf('Could not fetch Leantime tickets: %s', $result['leantimeError']));
        }

        return $this->render('admin/repo_advisory/index.html.twig', [
            'rows' => $result['rows'],
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

        try {
            $result = $this->repoAdvisoryService->createSecurityTicketForCodeOwner($codeOwnerId, $leantimeProjectId);

            if ($result['unassigned']) {
                $this->addFlash('warning', sprintf(
                    'Code owner %s has no matching Leantime user (email: %s); creating ticket unassigned.',
                    $result['codeOwner']->getName(),
                    $result['codeOwner']->getEmail(),
                ));
            }

            $this->addFlash('info', sprintf('Created Leantime ticket #%d.', $result['ticketId']));
        } catch (\Throwable $e) {
            $this->addFlash('error', sprintf('Failed to create Leantime ticket: %s', $e->getMessage()));
        }

        return $this->redirectToRoute('admin_repo_advisories');
    }
}
