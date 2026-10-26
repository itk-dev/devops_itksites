<?php

declare(strict_types=1);

namespace App\Tests\Service;

use App\Entity\AdvisoryAssessment;
use App\Entity\Site;
use App\Service\AdvisoryAssessor;
use Doctrine\ORM\EntityManagerInterface;
use Hautelook\AliceBundle\PhpUnit\RefreshDatabaseTrait;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

/**
 * Runs against the fixture sites: example-org/shop is checked out on stg
 * (twig) and prod (twig and http-foundation), and intranet.example.com has its
 * commonmark advisory marked as not affected.
 */
class AdvisoryAssessorTest extends KernelTestCase
{
    use RefreshDatabaseTrait;

    private const string TWIG = 'PKSA-b4n7-2wqz-8c5r';
    private const string HTTP_FOUNDATION = 'PKSA-x9q2-7fhd-3k1m';

    private EntityManagerInterface $entityManager;
    private AdvisoryAssessor $assessor;

    protected function setUp(): void
    {
        self::bootKernel();
        $this->entityManager = self::getContainer()->get(EntityManagerInterface::class);
        $this->assessor = self::getContainer()->get(AdvisoryAssessor::class);
    }

    public function testAMarkAppliesToEveryCheckoutOfTheRepo(): void
    {
        $this->assertSame(2, $this->assessor->openCount($this->site('shop.example.com')));
        $this->assertSame(1, $this->assessor->openCount($this->site('stg.shop.example.com')));

        $this->assessor->markNotAffected($this->site('shop.example.com'), [self::TWIG], 'Templates are not user editable', 'user@example.com');

        // A new advisory, here http-foundation, still counts.
        $this->assertSame(1, $this->assessor->openCount($this->site('shop.example.com')));
        $this->assertSame(0, $this->assessor->openCount($this->site('stg.shop.example.com')));
    }

    public function testOnlyTheSitesOpenAdvisoriesAreMarked(): void
    {
        $marked = $this->assessor->markNotAffected($this->site('stg.shop.example.com'), [self::TWIG, self::HTTP_FOUNDATION], 'Reviewed', 'user@example.com');

        $this->assertSame(1, $marked);
        $this->assertSame(1, $this->assessor->openCount($this->site('shop.example.com')), 'http-foundation is not on stg');
    }

    public function testAMarkRecordsTheReasonAndWhoMadeIt(): void
    {
        $this->assessor->markNotAffected($this->site('shop.example.com'), [self::TWIG], ' Templates are not user editable ', 'user@example.com');

        $assessment = $this->entityManager->getRepository(AdvisoryAssessment::class)->findOneBy(['advisoryId' => self::TWIG]);
        $this->assertSame(['github.com', 'example-org', 'shop'], [$assessment->getRepoProvider(), $assessment->getRepoOrganization(), $assessment->getRepoName()]);
        $this->assertSame('Templates are not user editable', $assessment->getReason());
        $this->assertSame('user@example.com', $assessment->getAssessedBy());
    }

    public function testAReasonIsRequired(): void
    {
        $this->expectException(\InvalidArgumentException::class);

        $this->assessor->markNotAffected($this->site('shop.example.com'), [self::TWIG], '  ', 'user@example.com');
    }

    public function testASiteWithoutAGitRepoCannotBeMarked(): void
    {
        $site = $this->site('release.example.com');
        $this->assertNull($this->assessor->gitRepo($site));

        $this->expectException(\InvalidArgumentException::class);

        $this->assessor->markNotAffected($site, ['SA-CORE-2099-001'], 'Reviewed', 'user@example.com');
    }

    public function testRemovingAMarkCountsTheAdvisoryAgain(): void
    {
        $site = $this->site('intranet.example.com');
        $this->assertSame(3, $this->assessor->openCount($site), 'The fixtures mark commonmark');

        $this->assessor->removeMark($site, 'PKSA-m3r8-5tyv-1h6p');

        $this->assertSame(4, $this->assessor->openCount($site));
    }

    public function testAdvisoriesListTheirMark(): void
    {
        $marks = [];
        foreach ($this->assessor->advisories($this->site('intranet.example.com')) as ['advisory' => $advisory, 'assessment' => $assessment]) {
            $marks[$advisory->getAdvisoryId()] = $assessment?->getReason();
        }
        ksort($marks);

        $this->assertSame([
            'PKSA-d8k2-6vnp-4j9t' => null,
            'PKSA-m3r8-5tyv-1h6p' => 'Markdown is only rendered from editor input',
            'SA-CONTRIB-2099-001' => null,
            'SA-CORE-2099-001' => null,
        ], $marks);
    }

    private function site(string $primaryDomain): Site
    {
        return $this->entityManager->getRepository(Site::class)->findOneBy(['primaryDomain' => $primaryDomain]);
    }
}
