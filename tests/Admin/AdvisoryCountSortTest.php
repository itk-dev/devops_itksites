<?php

declare(strict_types=1);

namespace App\Tests\Admin;

use App\Admin\AdvisoryCountSort;
use App\Entity\Site;
use Doctrine\ORM\EntityManagerInterface;
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

    private function queryBuilder(): \Doctrine\ORM\QueryBuilder
    {
        return self::getContainer()->get(EntityManagerInterface::class)
            ->getRepository(Site::class)
            ->createQueryBuilder('entity');
    }

    private function searchDto(array $customSort): SearchDto
    {
        return new SearchDto(new Request(), null, null, [], $customSort, null);
    }
}
