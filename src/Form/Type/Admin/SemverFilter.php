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
        // the "value" field as a scalar, getComparison() returns the operator,
        // and getValue2() returns the optional second value (used for between).
        $value = trim((string) $filterDataDto->getValue());
        $value2 = trim((string) $filterDataDto->getValue2());
        $comparison = $filterDataDto->getComparison();

        if ('' === $value || !in_array($comparison, SemverFilterType::COMPARISON_CHOICES, true)) {
            return;
        }

        $isRange = SemverFilterType::COMPARISON_BETWEEN === $comparison
            || SemverFilterType::COMPARISON_BETWEEN_EXCLUSIVE === $comparison;
        if ($isRange && '' === $value2) {
            return;
        }

        try {
            $parser = new VersionParser();
            $parser->normalize($value);
            if ($isRange) {
                $parser->normalize($value2);
            }
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
        if ($isRange) {
            [$lowerOp, $upperOp] = SemverFilterType::COMPARISON_BETWEEN === $comparison
                ? ['>=', '<=']
                : ['>', '<'];

            $queryBuilder
                ->andWhere(sprintf(
                    'SEMVER_NUMERIC(%1$s.%2$s) %3$s :%4$s_min AND SEMVER_NUMERIC(%1$s.%2$s) %5$s :%4$s_max',
                    $alias,
                    $property,
                    $lowerOp,
                    $parameter,
                    $upperOp,
                ))
                ->setParameter($parameter.'_min', self::toSemverNumeric($value))
                ->setParameter($parameter.'_max', self::toSemverNumeric($value2))
            ;

            return;
        }

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
