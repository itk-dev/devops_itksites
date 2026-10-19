<?php

declare(strict_types=1);

namespace App\Tests\Controller\Admin;

use App\Controller\Admin\AdvisoryCrudController;
use App\Controller\Admin\ModuleCrudController;
use App\Controller\Admin\ModuleVersionCrudController;
use App\Controller\Admin\PackageCrudController;
use App\Controller\Admin\PackageVersionCrudController;
use App\Entity\User;
use App\Message\CheckDrupalReleases;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Router\AdminUrlGenerator;
use Hautelook\AliceBundle\PhpUnit\RefreshDatabaseTrait;
use PHPUnit\Framework\Attributes\DataProvider;
use Psr\Cache\CacheItemPoolInterface;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\Messenger\Envelope;
use Symfony\Component\Messenger\Transport\InMemory\InMemoryTransport;

class DrupalReleaseCheckControllerTest extends WebTestCase
{
    use RefreshDatabaseTrait;

    private const string BUTTON = '.action-checkDrupalReleases[data-action-confirmation="true"]';

    #[DataProvider('pageProvider')]
    public function testPageOffersTheCheckWithConfirmation(string $page): void
    {
        $client = $this->loggedInClient();

        $client->request('GET', $this->url($page));

        $this->assertResponseIsSuccessful();
        $this->assertSelectorExists(self::BUTTON);
        $this->assertSelectorExists('#modal-action-confirmation');
        $this->assertStringContainsString(
            'Refresh the page to see the results.',
            (string) $client->getCrawler()->filter(self::BUTTON)->attr('data-action-confirmation-content'),
        );
        $this->assertStringContainsString('/admin/drupal/check-releases?token=', $this->checkUrl($client));
    }

    /**
     * @return iterable<string, array{string}>
     */
    public static function pageProvider(): iterable
    {
        yield 'Packages' => [PackageCrudController::class];
        yield 'Package versions' => [PackageVersionCrudController::class];
        yield 'Advisories' => [AdvisoryCrudController::class];
        yield 'Repo advisories' => ['admin_repo_advisories'];
        yield 'Modules' => [ModuleCrudController::class];
        yield 'Module versions' => [ModuleVersionCrudController::class];
    }

    public function testCheckClearsTheCacheQueuesTheCheckAndReturnsToThePage(): void
    {
        $client = $this->loggedInClient();
        $page = $this->url(PackageCrudController::class);
        $client->request('GET', $page);

        $cache = static::getContainer()->get('cache.drupal_org');
        $this->assertInstanceOf(CacheItemPoolInterface::class, $cache);
        $cache->save($cache->getItem('probe')->set('stale'));

        $client->request('POST', $this->checkUrl($client), server: ['HTTP_REFERER' => $page]);

        $this->assertResponseRedirects($page);
        $this->assertFalse($cache->hasItem('probe'));
        $this->assertSame([CheckDrupalReleases::class], $this->sentMessages());

        $client->followRedirect();
        $this->assertSelectorTextContains('#flash-messages', 'Refresh the page in a few minutes');
    }

    public function testCheckRejectsAnInvalidToken(): void
    {
        $client = $this->loggedInClient();

        $client->request('POST', '/admin/drupal/check-releases?token=invalid');

        $this->assertResponseStatusCodeSame(403);
        $this->assertSame([], $this->sentMessages());
    }

    public function testCheckDoesNotRedirectOffSite(): void
    {
        $client = $this->loggedInClient();
        $client->request('GET', $this->url(PackageCrudController::class));

        $client->request('POST', $this->checkUrl($client), server: ['HTTP_REFERER' => 'https://example.com/admin']);

        $this->assertResponseRedirects('/admin');
    }

    private function loggedInClient(): KernelBrowser
    {
        $client = static::createClient();
        // The in-memory transport and the cache pool live in the container.
        $client->disableReboot();
        $client->loginUser(static::getContainer()->get('doctrine')->getManager()->getRepository(User::class)->findOneBy([]));

        return $client;
    }

    /**
     * A CRUD controller class, or the name of a custom admin route.
     */
    private function url(string $page): string
    {
        if (!class_exists($page)) {
            return static::getContainer()->get('router')->generate($page);
        }

        return static::getContainer()->get(AdminUrlGenerator::class)
            ->setController($page)
            ->setAction(Crud::PAGE_INDEX)
            ->generateUrl();
    }

    private function checkUrl(KernelBrowser $client): string
    {
        return (string) $client->getCrawler()->filter(self::BUTTON)->closest('form')?->attr('action');
    }

    /**
     * @return list<class-string>
     */
    private function sentMessages(): array
    {
        $transport = static::getContainer()->get('messenger.transport.async');
        $this->assertInstanceOf(InMemoryTransport::class, $transport);

        return array_map(static fn (Envelope $envelope): string => $envelope->getMessage()::class, array_values($transport->getSent()));
    }
}
