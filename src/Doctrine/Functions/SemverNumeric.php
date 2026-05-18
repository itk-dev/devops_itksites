<?php

declare(strict_types=1);

namespace App\Doctrine\Functions;

use Doctrine\ORM\Query\AST\Functions\FunctionNode;
use Doctrine\ORM\Query\AST\Node;
use Doctrine\ORM\Query\Parser;
use Doctrine\ORM\Query\SqlWalker;
use Doctrine\ORM\Query\TokenType;

/**
 * SEMVER_NUMERIC(versionString) — MariaDB DQL function returning a sortable
 * BIGINT for a dotted-version string. Padding the string with ".0.0.0" lets
 * us treat shorter versions ("10", "10.5") as if all four segments were
 * present. Each segment is given 10^4 of room (max 9999 per segment),
 * packed left-to-right, so "10.5.9" becomes
 * 10·10^12 + 5·10^8 + 9·10^4 = 10_000_500_090_000.
 *
 * The 10^4 cap keeps the maximum value (~10^16) well inside PHP's
 * 64-bit PHP_INT_MAX (~9.22·10^18), so callers can mirror this arithmetic
 * in PHP to bind a single BIGINT parameter rather than feeding the
 * raw string back through the DQL function (which would expand the
 * argument-placeholder several times — see SemverFilter::toSemverNumeric()).
 *
 * Non-semver inputs (anything that doesn't match the dotted-number shape)
 * collapse to NULL via the outer CASE so they're excluded from any
 * comparison.
 */
class SemverNumeric extends FunctionNode
{
    private Node $versionExpression;

    #[\Override]
    public function parse(Parser $parser): void
    {
        $parser->match(TokenType::T_IDENTIFIER);
        $parser->match(TokenType::T_OPEN_PARENTHESIS);
        $this->versionExpression = $parser->StringPrimary();
        $parser->match(TokenType::T_CLOSE_PARENTHESIS);
    }

    #[\Override]
    public function getSql(SqlWalker $sqlWalker): string
    {
        $expr = $this->versionExpression->dispatch($sqlWalker);
        // Strip an optional leading "v" or "V" so "v5.5.40" parses identically to "5.5.40".
        $stripped = sprintf("TRIM(LEADING 'v' FROM TRIM(LEADING 'V' FROM %s))", $expr);
        $padded = sprintf("CONCAT(%s, '.0.0.0')", $stripped);
        $segment = static fn (int $n): string => sprintf(
            "CAST(SUBSTRING_INDEX(SUBSTRING_INDEX(%s, '.', %d), '.', -1) AS UNSIGNED)",
            $padded,
            $n,
        );

        // Return NULL for anything that isn't a dotted-number sequence so non-semver
        // rows ("?", "unknown", "main", "release-1.0", ...) get NULL comparisons
        // and are excluded from the result set regardless of operator.
        return sprintf(
            "(CASE WHEN %s REGEXP '^[vV]?[0-9]+(\\\\.[0-9]+){0,3}\$' "
            .'THEN (%s * 1000000000000 + %s * 100000000 + %s * 10000 + %s) '
            .'ELSE NULL END)',
            $expr,
            $segment(1),
            $segment(2),
            $segment(3),
            $segment(4),
        );
    }
}
