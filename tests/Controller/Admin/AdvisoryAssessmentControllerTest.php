<?php

declare(strict_types=1);

namespace App\Tests\Controller\Admin;

use App\Controller\Admin\SiteCrudController;
use App\Entity\AdvisoryAssessment;
use App\Entity\Site;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Router\AdminUrlGenerator;
use Hautelook\AliceBundle\PhpUnit\RefreshDatabaseTrait;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\DomCrawler\Crawler;
use Symfony\Component\DomCrawler\Form;

class AdvisoryAssessmentControllerTest extends WebTestCase
{
    use RefreshDatabaseTrait;

    private const string TWIG = 'PKSA-b4n7-2wqz-8c5r';
    // The server type column has red badges too.
    private const string BADGE = 'td[data-column="advisoryCount"] .badge';

    public function testTheDetailPageOffersTheMarksForTheRepo(): void
    {
        $client = $this->loggedInClient();

        $crawler = $client->request('GET', $this->detailUrl('shop.example.com'));

        $this->assertResponseIsSuccessful();
        $this->assertSelectorTextContains('.advisory-assessments', 'Marks apply to all checkouts of example-org/shop');
        $this->assertCount(2, $crawler->filter('form.advisory-mark'), 'One per open advisory');
        $this->assertSelectorExists('form.advisory-accept-all');
    }

    public function testMarkingAnAdvisoryAsNotAffected(): void
    {
        $client = $this->loggedInClient();
        $crawler = $client->request('GET', $this->detailUrl('shop.example.com'));

        $client->submit($this->markForm($crawler, self::TWIG), ['reason' => 'Templates are not user editable']);

        $this->assertResponseRedirects($this->detailUrl('shop.example.com'));
        $crawler = $client->followRedirect();
        $this->assertSelectorTextContains('#flash-messages', 'Marked 1 advisory as not affected for all checkouts of example-org/shop');
        $row = $this->row($crawler, self::TWIG);
        $this->assertStringContainsString('Not affected', $row->text());
        $this->assertStringContainsString('Templates are not user editable', $row->text());
        $this->assertStringContainsString('admin@example.com', $row->text());
        $this->assertCount(1, $row->filter('form.advisory-remove'));

        $assessment = $this->entityManager()->getRepository(AdvisoryAssessment::class)->findOneBy(['advisoryId' => self::TWIG]);
        $this->assertSame('admin@example.com', $assessment?->getAssessedBy());
    }

    public function testResubmittingAMarkSaysItIsAlreadyMarked(): void
    {
        $client = $this->loggedInClient();
        $crawler = $client->request('GET', $this->detailUrl('shop.example.com'));
        $form = $this->markForm($crawler, self::TWIG);
        $client->submit($form, ['reason' => 'Reviewed']);

        $client->submit($form, ['reason' => 'Reviewed']);

        $client->followRedirect();
        $this->assertSelectorTextContains('#flash-messages', 'already marked as not affected');
        $this->assertSelectorTextNotContains('#flash-messages', 'Marked 0');
    }

    public function testAcceptingAllCurrentAdvisories(): void
    {
        $client = $this->loggedInClient();
        $crawler = $client->request('GET', $this->detailUrl('shop.example.com'));

        $client->submit($crawler->filter('form.advisory-accept-all')->form(), ['reason' => 'Reviewed with the client']);

        $crawler = $client->followRedirect();
        $this->assertSelectorTextContains('#flash-messages', 'Marked 2 advisories as not affected');
        $this->assertCount(0, $crawler->filter('form.advisory-mark'));
        $this->assertCount(0, $crawler->filter('form.advisory-accept-all'), 'Nothing left to accept');
    }

    public function testAReasonIsRequired(): void
    {
        $client = $this->loggedInClient();
        $crawler = $client->request('GET', $this->detailUrl('shop.example.com'));

        $client->submit($this->markForm($crawler, self::TWIG), ['reason' => ' ']);

        $client->followRedirect();
        $this->assertSelectorTextContains('#flash-messages', 'A reason is required');
        $this->assertSame(0, $this->entityManager()->getRepository(AdvisoryAssessment::class)->count(['advisoryId' => self::TWIG]));
    }

    public function testRemovingAMark(): void
    {
        $client = $this->loggedInClient();
        $crawler = $client->request('GET', $this->detailUrl('intranet.example.com'));

        $client->submit($this->row($crawler, 'PKSA-m3r8-5tyv-1h6p')->filter('form.advisory-remove')->form());

        $crawler = $client->followRedirect();
        $this->assertSelectorTextContains('#flash-messages', 'Removed the mark');
        $this->assertCount(1, $this->row($crawler, 'PKSA-m3r8-5tyv-1h6p')->filter('form.advisory-mark'));
    }

    public function testAnInvalidTokenIsRejected(): void
    {
        $client = $this->loggedInClient();
        $crawler = $client->request('GET', $this->detailUrl('shop.example.com'));
        $form = $this->markForm($crawler, self::TWIG);

        $client->request('POST', $form->getUri(), ['advisoryIds' => [self::TWIG], 'reason' => 'Reviewed', '_token' => 'invalid']);

        $this->assertResponseStatusCodeSame(403);
    }

    public function testASiteWithoutAGitRepoExplainsWhyItCannotBeMarked(): void
    {
        $client = $this->loggedInClient();

        $crawler = $client->request('GET', $this->detailUrl('release.example.com'));

        $this->assertResponseIsSuccessful();
        $this->assertSelectorTextContains('.advisory-assessments', 'No git repo linked');
        $this->assertCount(0, $crawler->filter('form.advisory-mark, form.advisory-accept-all'));
        $this->assertGreaterThan(0, $crawler->filter('.advisory-assessments tbody tr')->count(), 'The advisories are still listed');
    }

    public function testTheIndexCountsOnlyOpenAdvisories(): void
    {
        $client = $this->loggedInClient();
        $crawler = $client->request('GET', $this->detailUrl('intranet.example.com'));
        $this->assertSame('3', $this->indexBadge($client, 'intranet.example.com'), 'Four advisories, one marked in the fixtures');

        // With all four marked, intranet has the most advisories but none open.
        $client->submit($crawler->filter('form.advisory-accept-all')->form(), ['reason' => 'Reviewed']);

        $this->assertSame('', $this->indexBadge($client, 'intranet.example.com'));
        $url = static::getContainer()->get(AdminUrlGenerator::class)
            ->setController(SiteCrudController::class)
            ->setAction(Crud::PAGE_INDEX)
            ->set('sort', ['advisoryCount' => 'DESC'])
            ->generateUrl();

        $crawler = $client->request('GET', $url);

        $this->assertResponseIsSuccessful();
        $counts = $crawler->filter('tbody tr[data-id]')->each(static fn (Crawler $row): int => (int) ($row->filter(self::BADGE)->count() > 0 ? $row->filter(self::BADGE)->text() : 0));
        $sorted = $counts;
        rsort($sorted);
        $this->assertSame($sorted, $counts, 'Sorted by the open count');
    }

    public function testTheRepoAdvisoriesPageCountsOnlyOpenAdvisories(): void
    {
        $client = $this->loggedInClient();

        $crawler = $client->request('GET', static::getContainer()->get('router')->generate('admin_repo_advisories'));

        $this->assertResponseIsSuccessful();
        $row = $crawler->filter('tr')->reduce(static fn (Crawler $row): bool => str_contains($row->text(), 'example-org/intranet'));
        $this->assertSame('3', $row->filter('.badge-danger')->text());
    }

    private function loggedInClient(): KernelBrowser
    {
        $client = static::createClient();
        // Keep the rows written by one request for the next.
        $client->disableReboot();
        $client->loginUser($this->entityManager()->getRepository(User::class)->findOneBy(['email' => 'admin@example.com']));

        return $client;
    }

    private function entityManager(): EntityManagerInterface
    {
        return static::getContainer()->get(EntityManagerInterface::class);
    }

    private function detailUrl(string $primaryDomain): string
    {
        $site = $this->entityManager()->getRepository(Site::class)->findOneBy(['primaryDomain' => $primaryDomain]);

        return static::getContainer()->get(AdminUrlGenerator::class)
            ->setController(SiteCrudController::class)
            ->setAction(Crud::PAGE_DETAIL)
            ->setEntityId($site?->getId())
            ->generateUrl();
    }

    private function row(Crawler $crawler, string $advisoryId): Crawler
    {
        return $crawler->filter('.advisory-assessments tbody tr[data-advisory-id="'.$advisoryId.'"]');
    }

    private function markForm(Crawler $crawler, string $advisoryId): Form
    {
        return $this->row($crawler, $advisoryId)->filter('form.advisory-mark')->form();
    }

    /**
     * The site's advisory badge on the index page, or '' without one.
     */
    private function indexBadge(KernelBrowser $client, string $primaryDomain): string
    {
        $url = static::getContainer()->get(AdminUrlGenerator::class)
            ->setController(SiteCrudController::class)
            ->setAction(Crud::PAGE_INDEX)
            ->generateUrl();
        $badge = $client->request('GET', $url)->filter('tbody tr[data-id]')
            ->reduce(static fn (Crawler $row): bool => str_contains($row->text(), $primaryDomain))
            ->filter(self::BADGE);

        return $badge->count() > 0 ? $badge->text() : '';
    }
}
