<?php

declare(strict_types=1);

namespace App\Admin;

use App\Entity\Advisory;
use App\Entity\AdvisoryAssessment;
use App\Entity\GitRepo;
use Doctrine\ORM\QueryBuilder;
use EasyCorp\Bundle\EasyAdminBundle\Config\Option\SortOrder;
use EasyCorp\Bundle\EasyAdminBundle\Dto\SearchDto;

final class AdvisoryCountSort
{
    public const string PROPERTY = 'advisoryCount';

    /**
     * Order the index by Site::getAdvisoryCount(), which is computed in PHP and
     * so has no column to sort on. EasyAdmin drops the sort from the URL before
     * it reaches the query — isValidCustomSort() only accepts mapped fields and
     * associations — and orders by a non-existent entity.advisoryCount column
     * when it is the default sort. So read it off the SearchDto, drop that
     * column, and order by a scalar sub query instead. Intended for use from a
     * CRUD controller's createIndexQueryBuilder() after the parent has built
     * the QB.
     */
    public static function apply(QueryBuilder $qb, SearchDto $searchDto): void
    {
        $rootAlias = (string) current($qb->getRootAliases());
        self::removeColumnOrder($qb, $rootAlias);

        // The property name is ours, but the direction comes from the URL and
        // reaches DQL as a string, so it is whitelisted rather than trusted.
        $direction = strtoupper((string) ($searchDto->getSort()[self::PROPERTY] ?? ''));
        if (SortOrder::ASC !== $direction && SortOrder::DESC !== $direction) {
            return;
        }

        $qb->addSelect(sprintf('(%s) AS HIDDEN %s', self::subQuery($rootAlias), self::PROPERTY));
        $qb->addOrderBy(self::PROPERTY, SortOrder::DESC === $direction ? \SortDirection::Descending : \SortDirection::Ascending);
    }

    /**
     * DQL counting the open advisories of the site aliased $siteAlias.
     *
     * An advisory belongs to a single package and an installation holds a
     * single version of it, so the advisory cannot be counted twice.
     * Advisories marked as not affected for the installation's git repo do
     * not count, as in AdvisoryAssessor::openCount().
     */
    public static function subQuery(string $siteAlias): string
    {
        return sprintf(
            'SELECT COUNT(adv.id) FROM %1$s adv JOIN adv.packageVersions advPackageVersion JOIN advPackageVersion.installations advInstallation WHERE advInstallation.id = IDENTITY(%2$s.installation) AND NOT EXISTS (SELECT advAssessment.id FROM %3$s advAssessment JOIN %4$s advRepo WITH advRepo.provider = advAssessment.repoProvider AND advRepo.organization = advAssessment.repoOrganization AND advRepo.repo = advAssessment.repoName JOIN advRepo.gitTags advTag JOIN advTag.installations advTagInstallation WHERE advTagInstallation.id = advInstallation.id AND advAssessment.advisoryId = adv.advisoryId)',
            Advisory::class,
            $siteAlias,
            AdvisoryAssessment::class,
            GitRepo::class,
        );
    }

    private static function removeColumnOrder(QueryBuilder $qb, string $rootAlias): void
    {
        $column = $rootAlias.'.'.self::PROPERTY.' ';
        $parts = [];
        foreach ($qb->getDQLPart('orderBy') as $orderBy) {
            foreach ($orderBy->getParts() as $part) {
                if (!str_starts_with($part, $column)) {
                    $parts[] = $part;
                }
            }
        }

        // A part reads "<sort> <direction>", and addOrderBy() wants them apart.
        $qb->resetDQLPart('orderBy');
        foreach ($parts as $part) {
            $at = (int) strrpos($part, ' ');
            $qb->addOrderBy(substr($part, 0, $at), substr($part, $at + 1));
        }
    }
}
