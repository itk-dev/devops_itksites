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
        // EasyAdmin's FilterDataDto splits the compound form: getValue() returns
        // the "value" field as a scalar, and getComparison() returns the operator.
        $value = trim((string) $filterDataDto->getValue());
        $comparison = $filterDataDto->getComparison();

        if ('' === $value || !isset(SemverFilterType::COMPARISON_CHOICES[$comparison])) {
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

        // The SEMVER_NUMERIC DQL function returns NULL for non-semver inputs,
        // and a NULL comparison is always falsy in SQL — so non-semver rows
        // (e.g. "?", "unknown", "main") are automatically excluded. We compute
        // the user-input numeric in PHP and bind it as a single BIGINT rather
        // than passing the string through SEMVER_NUMERIC again — otherwise
        // Doctrine would emit the placeholder five times (the function
        // references its argument five times) and PDO would complain about
        // bound-variable count.
        $queryBuilder
            ->andWhere(sprintf(
                'SEMVER_NUMERIC(%1$s.%2$s) %3$s :%4$s',
                $alias,
                $property,
                $comparison,
                $parameter,
            ))
            ->setParameter($parameter, self::toSemverNumeric($value))
        ;
    }

    /**
     * Mirrors the SEMVER_NUMERIC DQL function: strips a leading v/V, splits
     * on '.', pads to four segments with zeros, then packs major/minor/patch/
     * extra into a BIGINT with 10^4 of headroom per segment. Max value is
     * ~10^16, comfortably below PHP_INT_MAX (~9.22·10^18).
     */
    private static function toSemverNumeric(string $version): int
    {
        $segments = explode('.', ltrim($version, 'vV'));
        $major = (int) $segments[0];
        $minor = (int) ($segments[1] ?? 0);
        $patch = (int) ($segments[2] ?? 0);
        $extra = (int) ($segments[3] ?? 0);

        return $major * 1_000_000_000_000
            + $minor * 100_000_000
            + $patch * 10_000
            + $extra;
    }
}
