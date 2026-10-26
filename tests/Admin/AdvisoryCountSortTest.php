<?php

declare(strict_types=1);

namespace App\Tests\Admin;

use App\Admin\AdvisoryCountSort;
use App\Entity\Site;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\QueryBuilder;
use EasyCorp\Bundle\EasyAdminBundle\Dto\SearchDto;
use PHPUnit\Framework\Attributes\DataProvider;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\HttpFoundation\Request;

class AdvisoryCountSortTest extends KernelTestCase
{
    public function testItOrdersByTheCountSubQuery(): void
    {
        $qb = $this->queryBuilder();

        AdvisoryCountSort::apply($qb, $this->searchDto(['advisoryCount' => 'DESC']));

        // The DQL has to parse, or the index page 500s.
        $dql = $qb->getQuery()->getDQL();
        $this->assertStringContainsString('AS HIDDEN advisoryCount', $dql);
        $this->assertStringContainsString('ORDER BY advisoryCount DESC', $dql);
        $this->assertNotEmpty($qb->getQuery()->getSQL());
    }

    /**
     * As the default sort, EasyAdmin orders by the column, which does not
     * exist, so the sub query has to replace it.
     */
    public function testItReplacesTheColumnOrderOfTheDefaultSort(): void
    {
        $qb = $this->queryBuilder()
            ->addOrderBy('entity.primaryDomain', 'ASC')
            ->addOrderBy('entity.advisoryCount', 'DESC');

        AdvisoryCountSort::apply($qb, $this->searchDto([], ['advisoryCount' => 'DESC']));

        $dql = $qb->getQuery()->getDQL();
        $this->assertStringContainsString('ORDER BY entity.primaryDomain ASC, advisoryCount DESC', $dql);
        $this->assertStringNotContainsString('entity.advisoryCount', $dql);
        $this->assertNotEmpty($qb->getQuery()->getSQL());
    }

    public function testTheUrlSortWinsOverTheDefaultSort(): void
    {
        $qb = $this->queryBuilder()->addOrderBy('entity.advisoryCount', 'DESC');

        AdvisoryCountSort::apply($qb, $this->searchDto(['advisoryCount' => 'ASC'], ['advisoryCount' => 'DESC']));

        $this->assertStringContainsString('ORDER BY advisoryCount ASC', $qb->getQuery()->getDQL());
    }

    /**
     * The direction reaches DQL as a string, so anything but ASC or DESC is
     * ignored rather than passed through.
     */
    #[DataProvider('rejectedSortProvider')]
    public function testItIgnoresAnythingElse(array $customSort): void
    {
        $qb = $this->queryBuilder();

        AdvisoryCountSort::apply($qb, $this->searchDto($customSort));

        $this->assertSame([], $qb->getDQLPart('orderBy'));
        $this->assertStringNotContainsString('HIDDEN', $qb->getQuery()->getDQL());
    }

    public static function rejectedSortProvider(): iterable
    {
        yield 'no sort' => [[]];
        yield 'another column' => [['primaryDomain' => 'ASC']];
        yield 'smuggled column' => [['advisoryCount' => 'ASC, entity.id DESC']];
        yield 'nonsense' => [['advisoryCount' => 'SIDEWAYS']];
    }

    private function queryBuilder(): QueryBuilder
    {
        return self::getContainer()->get(EntityManagerInterface::class)
            ->getRepository(Site::class)
            ->createQueryBuilder('entity');
    }

    private function searchDto(array $customSort, array $defaultSort = []): SearchDto
    {
        return new SearchDto(new Request(), null, null, $defaultSort, $customSort, null);
    }
}
