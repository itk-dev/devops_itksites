<?php

declare(strict_types=1);

namespace App\Tests\Form\Type\Admin;

use App\Entity\Installation;
use App\Form\Type\Admin\SemverFilter;
use App\Form\Type\Admin\SemverFilterType;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\QueryBuilder;
use EasyCorp\Bundle\EasyAdminBundle\Dto\EntityDto;
use EasyCorp\Bundle\EasyAdminBundle\Dto\FilterDataDto;
use EasyCorp\Bundle\EasyAdminBundle\Dto\FilterDto;
use PHPUnit\Framework\Attributes\DataProvider;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

/**
 * Regression test for SemverFilter::apply().
 *
 * The bug: apply() expected $filterDataDto->getValue() to return the full
 * compound-form array, but EasyAdmin's FilterDataDto already splits the
 * compound form's children into getValue() (the value scalar) and
 * getComparison() (the operator). With the bug, apply() returned early
 * because is_array($scalar) is false, so no SEMVER_NUMERIC constraint
 * was ever added to the query.
 */
class SemverFilterTest extends KernelTestCase
{
    #[DataProvider('semverFilterCases')]
    public function testApplyAddsSemverConstraint(string $comparison, string $userValue, int $expectedNumeric): void
    {
        $qb = $this->makeInstallationQueryBuilder();

        $this->apply($qb, $comparison, $userValue);

        $dql = $qb->getDQL();
        self::assertStringContainsString('SEMVER_NUMERIC(entity.frameworkVersion)', $dql, 'SEMVER_NUMERIC clause must be added on the column');
        self::assertStringContainsString(' '.$comparison.' :frameworkVersion_0', $dql, 'Must use the requested operator and a single bound parameter');
        self::assertSame($expectedNumeric, $qb->getParameter('frameworkVersion_0')?->getValue(), 'User input must be pre-computed to a BIGINT and bound once');
    }

    public function testApplyReturnsEarlyWhenValueIsEmpty(): void
    {
        $qb = $this->makeInstallationQueryBuilder();
        $dqlBefore = $qb->getDQL();

        $this->apply($qb, '>', '');

        self::assertSame($dqlBefore, $qb->getDQL(), 'No constraint should be added when value is empty');
    }

    public function testApplyShortCircuitsToNoResultsOnInvalidInput(): void
    {
        $qb = $this->makeInstallationQueryBuilder();

        $this->apply($qb, '>', 'not-a-version');

        self::assertStringContainsString('1 = 0', $qb->getDQL(), 'Invalid user input must produce a 0-row query');
    }

    public function testApplyHandlesExclusiveBetween(): void
    {
        $qb = $this->makeInstallationQueryBuilder();

        $this->apply($qb, 'between_exclusive', '10', '11.3');

        $dql = $qb->getDQL();
        self::assertStringContainsString('SEMVER_NUMERIC(entity.frameworkVersion) > :frameworkVersion_0_min', $dql, 'Exclusive range must use > on the lower bound');
        self::assertStringContainsString('SEMVER_NUMERIC(entity.frameworkVersion) < :frameworkVersion_0_max', $dql, 'Exclusive range must use < on the upper bound');
        self::assertSame(10_000_000_000_000, $qb->getParameter('frameworkVersion_0_min')?->getValue());
        self::assertSame(11_000_300_000_000, $qb->getParameter('frameworkVersion_0_max')?->getValue());
    }

    public function testApplyHandlesInclusiveBetween(): void
    {
        $qb = $this->makeInstallationQueryBuilder();

        $this->apply($qb, 'between', '10', '11.3');

        $dql = $qb->getDQL();
        self::assertStringContainsString('SEMVER_NUMERIC(entity.frameworkVersion) >= :frameworkVersion_0_min', $dql, 'Inclusive range must use >= on the lower bound');
        self::assertStringContainsString('SEMVER_NUMERIC(entity.frameworkVersion) <= :frameworkVersion_0_max', $dql, 'Inclusive range must use <= on the upper bound');
    }

    public function testApplyReturnsEarlyForRangeWithoutUpperBound(): void
    {
        $qb = $this->makeInstallationQueryBuilder();
        $dqlBefore = $qb->getDQL();

        $this->apply($qb, 'between', '10', '');

        self::assertSame($dqlBefore, $qb->getDQL(), 'Missing upper bound on a range filter must be a no-op');
    }

    public function testApplyShortCircuitsRangeOnInvalidUpperBound(): void
    {
        $qb = $this->makeInstallationQueryBuilder();

        $this->apply($qb, 'between_exclusive', '10', 'oops');

        self::assertStringContainsString('1 = 0', $qb->getDQL(), 'Invalid upper-bound input must produce a 0-row query');
    }

    /**
     * @return iterable<string, array{string, string, int}>
     */
    public static function semverFilterCases(): iterable
    {
        yield 'less-than-or-equal' => ['<=', '10.6.3', 10_000_600_030_000];
        yield 'greater-than' => ['>', '10.0.0', 10_000_000_000_000];
        yield 'equals' => ['=', '10.5.9', 10_000_500_090_000];
        yield 'v-prefix accepted' => ['<=', 'v5.5.40', 5_000_500_400_000];
    }

    private function apply(QueryBuilder $qb, string $comparison, string $userValue, string $userValue2 = ''): void
    {
        $filterDto = new FilterDto();
        $filterDto->setProperty('frameworkVersion');
        $filterDto->setFormType(SemverFilterType::class);

        SemverFilter::new('frameworkVersion')->apply(
            $qb,
            FilterDataDto::new(0, $filterDto, 'entity', [
                'comparison' => $comparison,
                'value' => $userValue,
                'value2' => $userValue2,
            ]),
            null,
            new EntityDto(Installation::class, $this->getEntityManager()->getClassMetadata(Installation::class)),
        );
    }

    private function makeInstallationQueryBuilder(): QueryBuilder
    {
        return $this->getEntityManager()->getRepository(Installation::class)->createQueryBuilder('entity');
    }

    private function getEntityManager(): EntityManagerInterface
    {
        return static::getContainer()->get('doctrine')->getManager();
    }
}
