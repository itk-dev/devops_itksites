<?php

declare(strict_types=1);

namespace App\Controller\Admin;

use App\Entity\Site;
use App\Service\AdvisoryAssessor;
use EasyCorp\Bundle\EasyAdminBundle\Attribute\AdminRoute;
use Symfony\Bridge\Doctrine\Attribute\MapEntity;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;

/**
 * Marks a site's advisories as not affected, or removes a mark, from the
 * advisories table on the site's detail page.
 */
class AdvisoryAssessmentController extends AbstractController
{
    public function __construct(
        private readonly AdvisoryAssessor $assessor,
    ) {
    }

    public static function csrfIntent(Site $site): string
    {
        return 'site_advisories_'.$site->getId();
    }

    #[AdminRoute(path: '/site/{id}/advisories/not-affected', name: 'site_advisories_not_affected', options: ['methods' => ['POST']])]
    public function markNotAffected(#[MapEntity(id: 'id')] Site $site, Request $request): RedirectResponse
    {
        $this->denyUnlessTokenIsValid($site, $request);

        try {
            $marked = $this->assessor->markNotAffected(
                $site,
                array_values(array_map(strval(...), $request->request->all('advisoryIds'))),
                $request->request->getString('reason'),
                (string) $this->getUser()?->getUserIdentifier(),
            );
            $this->addFlash('success', sprintf(
                'Marked %d %s as not affected for all checkouts of %s.',
                $marked,
                1 === $marked ? 'advisory' : 'advisories',
                $this->assessor->gitRepo($site),
            ));
        } catch (\InvalidArgumentException $exception) {
            $this->addFlash('danger', $exception->getMessage());
        }

        return $this->redirectToSite($site);
    }

    #[AdminRoute(path: '/site/{id}/advisories/remove-mark', name: 'site_advisories_remove_mark', options: ['methods' => ['POST']])]
    public function removeMark(#[MapEntity(id: 'id')] Site $site, Request $request): RedirectResponse
    {
        $this->denyUnlessTokenIsValid($site, $request);

        $advisoryId = $request->request->getString('advisoryId');
        $this->assessor->removeMark($site, $advisoryId);
        $this->addFlash('success', sprintf('Removed the mark on %s, so it counts again.', $advisoryId));

        return $this->redirectToSite($site);
    }

    private function denyUnlessTokenIsValid(Site $site, Request $request): void
    {
        if (!$this->isCsrfTokenValid(self::csrfIntent($site), $request->request->getString('_token'))) {
            throw $this->createAccessDeniedException('Invalid CSRF token.');
        }
    }

    private function redirectToSite(Site $site): RedirectResponse
    {
        return $this->redirectToRoute('admin_site_detail', ['entityId' => $site->getId()]);
    }
}
