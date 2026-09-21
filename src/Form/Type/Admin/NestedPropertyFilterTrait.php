<?php

declare(strict_types=1);

namespace App\Form\Type\Admin;

use Doctrine\ORM\QueryBuilder;
use EasyCorp\Bundle\EasyAdminBundle\Dto\FilterDataDto;

/**
 * Equality filtering for a property that may sit across a relation.
 *
 * EasyAdmin does not auto-join nested-property filters, so a filter declared
 * on "server.hostingProvider" has to join the relation itself.
 */
trait NestedPropertyFilterTrait
{
    /**
     * Apply "<property> = <value>", joining the relation first when the
     * property crosses one (e.g. "server.type").
     *
     * The plain relation name is reused as the join alias to match EasyAdmin's
     * own sort-side auto-join (see EntityRepository::addOrderClause), so
     * sorting and filtering on the same association produce a single JOIN.
     * Background: https://github.com/EasyCorp/EasyAdminBundle/issues/4120.
     */
    private function applyEqualsAcrossRelation(QueryBuilder $queryBuilder, FilterDataDto $filterDataDto): void
    {
        $alias = $filterDataDto->getEntityAlias();
        $property = $filterDataDto->getProperty();
        $parameter = $filterDataDto->getParameterName();

        if (str_contains($property, '.')) {
            [$relation, $property] = explode('.', $property, 2);

            if (!in_array($relation, $queryBuilder->getAllAliases(), true)) {
                $queryBuilder->leftJoin($alias.'.'.$relation, $relation);
            }

            $alias = $relation;
        }

        $queryBuilder
            ->andWhere(sprintf('%s.%s = :%s', $alias, $property, $parameter))
            ->setParameter($parameter, $filterDataDto->getValue());
    }
}
