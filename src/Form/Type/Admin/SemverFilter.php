<?php

declare(strict_types=1);

namespace App\Form\Type\Admin;

use Composer\Semver\VersionParser;
use Doctrine\ORM\QueryBuilder;
use EasyCorp\Bundle\EasyAdminBundle\Contracts\Filter\FilterInterface;
use EasyCorp\Bundle\EasyAdminBundle\Dto\EntityDto;
use EasyCorp\Bundle\EasyAdminBundle\Dto\FieldDto;
use EasyCorp\Bundle\EasyAdminBundle\Dto\FilterDataDto;
use EasyCorp\Bundle\EasyAdminBundle\Filter\FilterTrait;
use Symfony\Contracts\Translation\TranslatableInterface;

class SemverFilter implements FilterInterface
{
    use FilterTrait;

    private const string VERSION_REGEXP = '^[vV]?[0-9]+(\\.[0-9]+){0,3}$';

    public static function new(string $propertyName, false|string|TranslatableInterface|null $label = null): self
    {
        return new self()
            ->setFilterFqcn(self::class)
            ->setProperty($propertyName)
            ->setLabel($label)
            ->setFormType(SemverFilterType::class);
    }

    public function apply(QueryBuilder $queryBuilder, FilterDataDto $filterDataDto, ?FieldDto $fieldDto, EntityDto $entityDto): void
    {
        $data = $filterDataDto->getValue();
        if (!is_array($data)) {
            return;
        }

        $value = isset($data['value']) ? trim((string) $data['value']) : '';
        $comparison = $data['comparison'] ?? null;

        if ('' === $value || !is_string($comparison) || !isset(SemverFilterType::COMPARISON_CHOICES[$comparison])) {
            return;
        }

        try {
            new VersionParser()->normalize($value);
        } catch (\UnexpectedValueException) {
            $queryBuilder->andWhere('1 = 0');

            return;
        }

        $alias = $filterDataDto->getEntityAlias();
        $property = $filterDataDto->getProperty();
        $parameter = $filterDataDto->getParameterName();

        $queryBuilder
            ->andWhere(sprintf('%1$s.%2$s REGEXP :%3$s_pattern', $alias, $property, $parameter))
            ->andWhere(sprintf(
                'SEMVER_NUMERIC(%1$s.%2$s) %3$s SEMVER_NUMERIC(:%4$s_value)',
                $alias,
                $property,
                $comparison,
                $parameter,
            ))
            ->setParameter($parameter.'_pattern', self::VERSION_REGEXP)
            ->setParameter($parameter.'_value', $value)
        ;
    }
}
