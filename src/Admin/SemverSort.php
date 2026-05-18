<?php

declare(strict_types=1);

namespace App\Admin;

use Doctrine\ORM\QueryBuilder;

final class SemverSort
{
    /**
     * Rewrite the QueryBuilder's ORDER BY so that the listed root-alias
     * properties sort in semver order (via the SEMVER_NUMERIC DQL function)
     * instead of lexicographic string order. Other ORDER BY parts are
     * left untouched. Intended for use from a CRUD controller's
     * createIndexQueryBuilder() after the parent has built the QB.
     */
    public static function apply(QueryBuilder $qb, string ...$properties): void
    {
        $orderByParts = $qb->getDQLPart('orderBy');
        if ([] === $orderByParts) {
            return;
        }

        $alias = (string) current($qb->getRootAliases());
        $needles = [];
        foreach ($properties as $property) {
            $needles[$alias.'.'.$property] = 'SEMVER_NUMERIC('.$alias.'.'.$property.')';
        }

        $qb->resetDQLPart('orderBy');
        foreach ($orderByParts as $orderBy) {
            foreach ($orderBy->getParts() as $part) {
                if (preg_match('/^(.+?)\s+(ASC|DESC)$/i', $part, $m)) {
                    [$expr, $dir] = [$m[1], strtoupper($m[2])];
                } else {
                    [$expr, $dir] = [$part, 'ASC'];
                }

                $expr = $needles[$expr] ?? $expr;
                $qb->addOrderBy($expr, $dir);
            }
        }
    }
}
