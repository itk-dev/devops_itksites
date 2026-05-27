<?php

declare(strict_types=1);

namespace App\Controller\Admin;

use App\Entity\CodeOwner;
use App\Entity\GitRepo;
use App\Repository\CodeOwnerRepository;
use App\Repository\GitRepoRepository;
use App\Repository\ProjectRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class RepoAdvisoryController extends AbstractController
{
    private const string CSRF_INTENT = 'repo_advisory_action';

    public function __construct(
        private readonly GitRepoRepository $gitRepoRepository,
        private readonly ProjectRepository $projectRepository,
        private readonly CodeOwnerRepository $codeOwnerRepository,
    ) {
    }

    #[Route('/admin/repo-advisories', name: 'admin_repo_advisories', methods: ['GET'])]
    public function index(): Response
    {
        $reposWithCount = $this->gitRepoRepository->findReposWithAdvisoryCount();

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

            $rows[] = [
                'repo' => $repo,
                'advisoryCount' => $entry['advisoryCount'],
                'projects' => $projects,
                'codeOwners' => array_values($codeOwners),
            ];
        }

        return $this->render('admin/repo_advisory/index.html.twig', [
            'rows' => $rows,
            'csrf_intent' => self::CSRF_INTENT,
        ]);
    }

    #[Route('/admin/repo-advisories/{repo}/print-codeowner', name: 'admin_repo_advisories_print_codeowner', methods: ['POST'])]
    public function printCodeOwner(Request $request, GitRepo $repo): RedirectResponse
    {
        if (!$this->isCsrfTokenValid(self::CSRF_INTENT, (string) $request->request->get('_token'))) {
            $this->addFlash('error', 'Invalid CSRF token; please retry.');

            return $this->redirectToRoute('admin_repo_advisories');
        }

        $codeOwnerId = (string) $request->request->get('codeOwnerId', '');
        if ('' === $codeOwnerId) {
            $this->addFlash('warning', 'No code owner selected.');

            return $this->redirectToRoute('admin_repo_advisories');
        }

        $codeOwner = $this->codeOwnerRepository->find($codeOwnerId);
        if (!$codeOwner instanceof CodeOwner) {
            $this->addFlash('error', 'Code owner not found.');

            return $this->redirectToRoute('admin_repo_advisories');
        }

        $this->addFlash('info', sprintf('Code owner: %s', $codeOwner->getName()));

        return $this->redirectToRoute('admin_repo_advisories');
    }
}
