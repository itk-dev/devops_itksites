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

        // When the user fills the upper-bound field together with a directional
        // operator (>, >=, <, <=), treat the filter as a range. The operator's
        // inclusivity carries over (>= / <= → inclusive, > / < → exclusive),
        // so the natural reading "from X to Y" works regardless of which side
        // the user picked. Range operators (between / between_exclusive)
        // require both values and behave the same way. = and != are exact
        // matches, so value2 is ignored.
        $isExplicitRange = in_array($comparison, [SemverFilterType::COMPARISON_BETWEEN, SemverFilterType::COMPARISON_BETWEEN_EXCLUSIVE], true);
        $autoRange = '' !== $value2 && in_array($comparison, [
            SemverFilterType::COMPARISON_GT,
            SemverFilterType::COMPARISON_GTE,
            SemverFilterType::COMPARISON_LT,
            SemverFilterType::COMPARISON_LTE,
        ], true);
        $isRange = $isExplicitRange || $autoRange;

        if ($isExplicitRange && '' === $value2) {
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
            $inclusive = in_array($comparison, [
                SemverFilterType::COMPARISON_BETWEEN,
                SemverFilterType::COMPARISON_GTE,
                SemverFilterType::COMPARISON_LTE,
            ], true);
            [$lowerOp, $upperOp] = $inclusive ? ['>=', '<='] : ['>', '<'];

            // Sort the two values numerically so the user can enter them in any
            // order — "< 11.3.0" with value2 = "10.0.0" still produces a sane
            // range, not an unsatisfiable WHERE.
            $a = self::toSemverNumeric($value);
            $b = self::toSemverNumeric($value2);
            [$min, $max] = $a <= $b ? [$a, $b] : [$b, $a];

            $queryBuilder
                ->andWhere(sprintf(
                    'SEMVER_NUMERIC(%1$s.%2$s) %3$s :%4$s_min AND SEMVER_NUMERIC(%1$s.%2$s) %5$s :%4$s_max',
                    $alias,
                    $property,
                    $lowerOp,
                    $parameter,
                    $upperOp,
                ))
                ->setParameter($parameter.'_min', $min)
                ->setParameter($parameter.'_max', $max)
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
