<?php

declare(strict_types=1);

namespace App\Tests\Admin;

use App\Admin\SemverSort;
use App\Entity\Installation;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class SemverSortTest extends KernelTestCase
{
    public function testRewritesMatchingPropertyToSemverNumeric(): void
    {
        $qb = $this->makeQb()->orderBy('entity.frameworkVersion', 'DESC');

        SemverSort::apply($qb, 'frameworkVersion');

        $parts = $qb->getDQLPart('orderBy');
        self::assertCount(1, $parts);
        self::assertSame('SEMVER_NUMERIC(entity.frameworkVersion) DESC', $parts[0]->getParts()[0]);
    }

    public function testKeepsDirectionAndAddsAscDefaultWhenMissing(): void
    {
        $qb = $this->makeQb()->orderBy('entity.frameworkVersion');

        SemverSort::apply($qb, 'frameworkVersion');

        self::assertSame('SEMVER_NUMERIC(entity.frameworkVersion) ASC', $qb->getDQLPart('orderBy')[0]->getParts()[0]);
    }

    public function testLeavesNonMatchingPropertiesUntouched(): void
    {
        $qb = $this->makeQb()
            ->orderBy('entity.rootDir', 'ASC')
            ->addOrderBy('entity.frameworkVersion', 'DESC');

        SemverSort::apply($qb, 'frameworkVersion');

        $allParts = [];
        foreach ($qb->getDQLPart('orderBy') as $orderBy) {
            $allParts = [...$allParts, ...$orderBy->getParts()];
        }
        self::assertSame(['entity.rootDir ASC', 'SEMVER_NUMERIC(entity.frameworkVersion) DESC'], $allParts);
    }

    public function testRewritesMultipleListedProperties(): void
    {
        $qb = $this->makeQb()
            ->orderBy('entity.frameworkVersion', 'DESC')
            ->addOrderBy('entity.composerVersion', 'ASC');

        SemverSort::apply($qb, 'frameworkVersion', 'composerVersion');

        $allParts = [];
        foreach ($qb->getDQLPart('orderBy') as $orderBy) {
            $allParts = [...$allParts, ...$orderBy->getParts()];
        }
        self::assertSame(
            ['SEMVER_NUMERIC(entity.frameworkVersion) DESC', 'SEMVER_NUMERIC(entity.composerVersion) ASC'],
            $allParts,
        );
    }

    public function testIsNoopWhenNoOrderByPresent(): void
    {
        $qb = $this->makeQb();

        SemverSort::apply($qb, 'frameworkVersion');

        self::assertSame([], $qb->getDQLPart('orderBy'));
    }

    private function makeQb(): \Doctrine\ORM\QueryBuilder
    {
        return $this->getEntityManager()->getRepository(Installation::class)->createQueryBuilder('entity');
    }

    private function getEntityManager(): EntityManagerInterface
    {
        return static::getContainer()->get('doctrine')->getManager();
    }
}
