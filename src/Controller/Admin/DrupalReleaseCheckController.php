<?php

declare(strict_types=1);

namespace App\Controller\Admin;

use App\Message\CheckDrupalReleases;
use EasyCorp\Bundle\EasyAdminBundle\Attribute\AdminRoute;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use Psr\Cache\CacheItemPoolInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\DependencyInjection\Attribute\Target;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Security\Csrf\CsrfTokenManagerInterface;

/**
 * Queues a check of all drupal/* packages against drupal.org, like
 * app:drupal:check-releases --refresh, from a global admin action.
 */
class DrupalReleaseCheckController extends AbstractController
{
    public const string CSRF_INTENT = 'drupal_check_releases';

    private const string ROUTE = 'admin_drupal_check_releases';

    public function __construct(
        private readonly MessageBusInterface $messageBus,
        #[Target('cache.drupal_org')]
        private readonly CacheItemPoolInterface $cache,
    ) {
    }

    /**
     * The global index action. renderAsForm() posts without a CSRF token, so
     * the token travels in the URL.
     */
    public static function action(CsrfTokenManagerInterface $csrfTokenManager): Action
    {
        return Action::new('checkDrupalReleases', 'Check drupal.org', 'fa fa-rotate')
            ->linkToRoute(self::ROUTE, ['token' => $csrfTokenManager->getToken(self::CSRF_INTENT)->getValue()])
            ->createAsGlobalAction()
            ->renderAsForm()
            ->askConfirmation('Check drupal.org for new releases and security advisories?', 'Start check')
            ->setHtmlAttributes([
                'data-action-confirmation-content' => 'The check runs in the background and takes a few minutes. Refresh the page to see the results.',
            ]);
    }

    #[AdminRoute(path: '/drupal/check-releases', name: 'drupal_check_releases', options: ['methods' => ['POST']])]
    public function checkReleases(Request $request): RedirectResponse
    {
        if (!$this->isCsrfTokenValid(self::CSRF_INTENT, (string) $request->query->get('token'))) {
            throw $this->createAccessDeniedException('Invalid CSRF token.');
        }

        $this->cache->clear();
        $this->messageBus->dispatch(new CheckDrupalReleases());

        $this->addFlash('info', 'Checking drupal.org for new releases and security advisories. Refresh the page in a few minutes to see the results.');

        return $this->redirect($this->backToAdmin($request));
    }

    /**
     * Back to the page the action was used on, but only within the admin, so
     * the Referer header cannot redirect off-site.
     */
    private function backToAdmin(Request $request): string
    {
        $referer = (string) $request->headers->get('referer');

        return str_starts_with($referer, $request->getSchemeAndHttpHost().'/admin') ? $referer : $this->generateUrl('admin');
    }
}
