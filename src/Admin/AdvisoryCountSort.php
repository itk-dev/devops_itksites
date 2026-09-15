<?php

declare(strict_types=1);

namespace App\Admin;

use App\Entity\Advisory;
use Doctrine\ORM\QueryBuilder;
use EasyCorp\Bundle\EasyAdminBundle\Config\Option\SortOrder;
use EasyCorp\Bundle\EasyAdminBundle\Dto\SearchDto;

final class AdvisoryCountSort
{
    public const string PROPERTY = 'advisoryCount';

    /**
     * Order the index by Site::getAdvisoryCount(), which is computed in PHP and
     * so has no column to sort on. EasyAdmin drops the sort before it reaches
     * the query — isValidCustomSort() only accepts mapped fields and
     * associations — so read it off the SearchDto and order by a scalar sub
     * query instead. Intended for use from a CRUD controller's
     * createIndexQueryBuilder() after the parent has built the QB.
     */
    public static function apply(QueryBuilder $qb, SearchDto $searchDto): void
    {
        // The property name is ours, but the direction comes from the URL and
        // reaches DQL as a string, so it is whitelisted rather than trusted.
        $direction = strtoupper((string) ($searchDto->getCustomSort()[self::PROPERTY] ?? ''));
        if (SortOrder::ASC !== $direction && SortOrder::DESC !== $direction) {
            return;
        }

        $rootAlias = (string) current($qb->getRootAliases());

        // An advisory belongs to a single package and an installation holds a
        // single version of it, so the advisory cannot be counted twice.
        $qb->addSelect(sprintf(
            '(SELECT COUNT(adv.id) FROM %s adv JOIN adv.packageVersions advPackageVersion JOIN advPackageVersion.installations advInstallation WHERE advInstallation.id = IDENTITY(%s.installation)) AS HIDDEN %s',
            Advisory::class,
            $rootAlias,
            self::PROPERTY
        ));

        $qb->addOrderBy(self::PROPERTY, $direction);
    }
}
