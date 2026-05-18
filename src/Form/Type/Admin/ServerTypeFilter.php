<?php

declare(strict_types=1);

namespace App\Form\Type\Admin;

use Doctrine\ORM\QueryBuilder;
use EasyCorp\Bundle\EasyAdminBundle\Contracts\Filter\FilterInterface;
use EasyCorp\Bundle\EasyAdminBundle\Dto\EntityDto;
use EasyCorp\Bundle\EasyAdminBundle\Dto\FieldDto;
use EasyCorp\Bundle\EasyAdminBundle\Dto\FilterDataDto;
use EasyCorp\Bundle\EasyAdminBundle\Filter\FilterTrait;
use Symfony\Contracts\Translation\TranslatableInterface;

class ServerTypeFilter implements FilterInterface
{
    use FilterTrait;

    public static function new(string $propertyName, false|string|TranslatableInterface|null $label = null): self
    {
        return new self()
            ->setFilterFqcn(self::class)
            ->setProperty($propertyName)
            ->setLabel($label)
            ->setFormType(ServerTypeFilterType::class);
    }

    public function apply(QueryBuilder $queryBuilder, FilterDataDto $filterDataDto, ?FieldDto $fieldDto, EntityDto $entityDto): void
    {
        $rootAlias = $filterDataDto->getEntityAlias();
        $property = $filterDataDto->getProperty();
        $parameter = $filterDataDto->getParameterName();
        $value = $filterDataDto->getValue();

        // EasyAdmin doesn't auto-join nested-property filters; if the
        // property crosses a relation (e.g. "server.type"), join it
        // ourselves and reference the joined alias. We reuse the plain
        // relation name as the join alias to match EA's own sort-side
        // auto-join (see EntityRepository::addOrderClause), so combining
        // sort + filter on the same association produces a single JOIN.
        // Background: https://github.com/EasyCorp/EasyAdminBundle/issues/4120.
        if (str_contains($property, '.')) {
            [$relation, $leafProperty] = explode('.', $property, 2);
            if (!in_array($relation, $queryBuilder->getAllAliases(), true)) {
                $queryBuilder->leftJoin($rootAlias.'.'.$relation, $relation);
            }
            $queryBuilder
                ->andWhere(sprintf('%s.%s = :%s', $relation, $leafProperty, $parameter))
                ->setParameter($parameter, $value);

            return;
        }

        $queryBuilder
            ->andWhere(sprintf('%s.%s = :%s', $rootAlias, $property, $parameter))
            ->setParameter($parameter, $value);
    }
}
