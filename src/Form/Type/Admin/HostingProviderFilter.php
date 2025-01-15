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

class HostingProviderFilter implements FilterInterface
{
    use FilterTrait;

    public static function new(string $propertyName, false|null|string|TranslatableInterface $label = null): self
    {
        return (new self())
            ->setFilterFqcn(self::class)
            ->setProperty($propertyName)
            ->setLabel($label)
            ->setFormType(HostingProviderFilterType::class);
    }

    public function apply(QueryBuilder $queryBuilder, FilterDataDto $filterDataDto, ?FieldDto $fieldDto, EntityDto $entityDto): void
    {
        $queryBuilder->andWhere(sprintf('%s.%s = :hostingProvider', $filterDataDto->getEntityAlias(), $filterDataDto->getProperty()))
            ->setParameter('hostingProvider', $filterDataDto->getValue());
    }
}
