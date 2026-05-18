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
 * present. Each segment is given 10^6 of room, packed left-to-right, so
 * "10.5.9" becomes 10·10^18 + 5·10^12 + 9·10^6 = 10005009000000.
 *
 * Non-numeric segments cast to 0 (with a MariaDB warning); callers should
 * combine this with a REGEXP guard if they need to exclude such rows.
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

        return sprintf(
            '(%s * 1000000000000000000 + %s * 1000000000000 + %s * 1000000 + %s)',
            $segment(1),
            $segment(2),
            $segment(3),
            $segment(4),
        );
    }
}
