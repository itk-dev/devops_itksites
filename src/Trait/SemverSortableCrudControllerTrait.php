<?php

declare(strict_types=1);

namespace App\Trait;

use App\Admin\SemverSort;
use Doctrine\ORM\QueryBuilder;
use EasyCorp\Bundle\EasyAdminBundle\Collection\FieldCollection;
use EasyCorp\Bundle\EasyAdminBundle\Collection\FilterCollection;
use EasyCorp\Bundle\EasyAdminBundle\Dto\EntityDto;
use EasyCorp\Bundle\EasyAdminBundle\Dto\SearchDto;

/**
 * Adds semver-aware ORDER BY rewriting to a CRUD controller. The using
 * controller declares which root-alias properties hold semver strings
 * via {@see semverSortedProperties()}; this trait then transparently
 * wraps those properties in SEMVER_NUMERIC() in the index query.
 */
trait SemverSortableCrudControllerTrait
{
    /**
     * @return list<string> property names on the root entity whose
     *                      ORDER BY should use semver order instead of
     *                      lexicographic string order
     */
    abstract protected function semverSortedProperties(): array;

    public function createIndexQueryBuilder(SearchDto $searchDto, EntityDto $entityDto, FieldCollection $fields, FilterCollection $filters): QueryBuilder
    {
        $qb = parent::createIndexQueryBuilder($searchDto, $entityDto, $fields, $filters);
        SemverSort::apply($qb, ...$this->semverSortedProperties());

        return $qb;
    }
}
