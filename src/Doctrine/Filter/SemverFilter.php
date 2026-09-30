<?php

declare(strict_types=1);

namespace App\Doctrine\Filter;

use ApiPlatform\Doctrine\Orm\Filter\FilterInterface;
use ApiPlatform\Doctrine\Orm\Util\QueryNameGeneratorInterface;
use ApiPlatform\Metadata\BackwardCompatibleFilterDescriptionTrait;
use ApiPlatform\Metadata\OpenApiParameterFilterInterface;
use ApiPlatform\Metadata\Operation;
use ApiPlatform\Metadata\Parameter;
use ApiPlatform\OpenApi\Model\Parameter as OpenApiParameter;
use App\Doctrine\Functions\SemverNumeric;
use Composer\Semver\VersionParser;
use Doctrine\ORM\QueryBuilder;

/**
 * API Platform filter comparing a version string column by semantic version,
 * the API counterpart of the EasyAdmin App\Form\Type\Admin\SemverFilter.
 *
 * ?phpVersion=8.3 matches exactly, ?phpVersion[gte]=8.1&phpVersion[lt]=8.4
 * combines operators, and ?phpVersion[between]=8.1..8.3 is inclusive. A value
 * that is not a version matches nothing.
 */
final readonly class SemverFilter implements FilterInterface, OpenApiParameterFilterInterface
{
    use BackwardCompatibleFilterDescriptionTrait;

    private const array OPERATORS = [
        'gt' => '>',
        'gte' => '>=',
        'lt' => '<',
        'lte' => '<=',
        'ne' => '!=',
    ];

    private const string BETWEEN = 'between';

    /**
     * Describe each operator with $label ("PHP version") in the OpenAPI
     * document. A description on the QueryParameter itself would replace all
     * of them with the same text.
     */
    public function __construct(private string $label = 'Version')
    {
    }

    /**
     * @param array<string, mixed> $context
     */
    #[\Override]
    public function apply(QueryBuilder $queryBuilder, QueryNameGeneratorInterface $queryNameGenerator, string $resourceClass, ?Operation $operation = null, array $context = []): void
    {
        /** @var Parameter $parameter */
        $parameter = $context['parameter'];
        $values = $parameter->getValue();
        $field = sprintf('SEMVER_NUMERIC(%s.%s)', $queryBuilder->getRootAliases()[0], $parameter->getProperty());

        foreach (is_array($values) ? $values : ['=' => $values] as $operator => $value) {
            if (!is_string($value) || '' === trim($value)) {
                continue;
            }

            if (self::BETWEEN === $operator) {
                $bounds = explode('..', $value, 2);
                if (2 !== count($bounds) || !$this->isVersion($bounds[0]) || !$this->isVersion($bounds[1])) {
                    $queryBuilder->andWhere('1 = 0');

                    return;
                }

                $a = SemverNumeric::toNumeric(trim($bounds[0]));
                $b = SemverNumeric::toNumeric(trim($bounds[1]));
                $min = $queryNameGenerator->generateParameterName($parameter->getKey());
                $max = $queryNameGenerator->generateParameterName($parameter->getKey());
                $queryBuilder
                    ->andWhere(sprintf('%s BETWEEN :%s AND :%s', $field, $min, $max))
                    ->setParameter($min, min($a, $b))
                    ->setParameter($max, max($a, $b));

                continue;
            }

            $sqlOperator = '=' === $operator ? '=' : (self::OPERATORS[$operator] ?? null);
            if (null === $sqlOperator) {
                continue;
            }

            if (!$this->isVersion($value)) {
                $queryBuilder->andWhere('1 = 0');

                return;
            }

            $name = $queryNameGenerator->generateParameterName($parameter->getKey());
            $queryBuilder
                ->andWhere(sprintf('%s %s :%s', $field, $sqlOperator, $name))
                ->setParameter($name, SemverNumeric::toNumeric(trim($value)));
        }
    }

    /**
     * @return list<OpenApiParameter>
     */
    #[\Override]
    public function getOpenApiParameters(Parameter $parameter): array
    {
        $key = $parameter->getKey();
        $descriptions = [
            $key => '%s equal to, e.g. 8.3',
            $key.'[gt]' => '%s greater than',
            $key.'[gte]' => '%s greater than or equal to',
            $key.'[lt]' => '%s less than',
            $key.'[lte]' => '%s less than or equal to',
            $key.'[ne]' => '%s not equal to',
            $key.'['.self::BETWEEN.']' => '%s between two versions, inclusive, e.g. 8.1..8.3',
        ];

        return array_map(
            fn (string $name, string $description): OpenApiParameter => new OpenApiParameter(
                name: $name,
                in: 'query',
                description: sprintf($description, $this->label),
                schema: ['type' => 'string'],
            ),
            array_keys($descriptions),
            $descriptions,
        );
    }

    private function isVersion(string $value): bool
    {
        try {
            new VersionParser()->normalize(trim($value));

            return true;
        } catch (\UnexpectedValueException) {
            return false;
        }
    }
}
