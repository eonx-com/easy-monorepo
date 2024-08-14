<?php
declare(strict_types=1);

namespace EonX\EasyDoctrine\Common\Function;

use Doctrine\ORM\Query\AST\Functions\FunctionNode;
use Doctrine\ORM\Query\AST\Literal;
use Doctrine\ORM\Query\AST\OrderByClause;
use Doctrine\ORM\Query\AST\PathExpression;
use Doctrine\ORM\Query\Parser;
use Doctrine\ORM\Query\SqlWalker;
use Doctrine\ORM\Query\TokenType;

final class StringAgg extends FunctionNode
{
    private Literal $delimiter;

    private PathExpression $expression;

    private bool $isDistinct = false;

    private ?OrderByClause $orderBy = null;

    /**
     * @throws \Doctrine\ORM\Query\QueryException
     */
    public function getSql(SqlWalker $sqlWalker): string
    {
        return \sprintf(
            'STRING_AGG(%s%s::CHARACTER VARYING, %s%s)',
            ($this->isDistinct ? 'DISTINCT ' : ''),
            $sqlWalker->walkPathExpression($this->expression),
            $sqlWalker->walkStringPrimary($this->delimiter),
            ($this->orderBy !== null ? $sqlWalker->walkOrderByClause($this->orderBy) : '')
        );
    }

    /**
     * @throws \Doctrine\ORM\Query\QueryException
     */
    public function parse(Parser $parser): void
    {
        $parser->match(TokenType::T_IDENTIFIER);
        $parser->match(TokenType::T_OPEN_PARENTHESIS);

        $lexer = $parser->getLexer();
        if ($lexer->isNextToken(TokenType::T_DISTINCT)) {
            $parser->match(TokenType::T_DISTINCT);

            $this->isDistinct = true;
        }

        $this->expression = $parser->PathExpression(PathExpression::TYPE_STATE_FIELD);
        $parser->match(TokenType::T_COMMA);
        /** @var \Doctrine\ORM\Query\AST\Literal $delimiter */
        $delimiter = $parser->StringPrimary();
        $this->delimiter = $delimiter;

        if ($lexer->isNextToken(TokenType::T_ORDER)) {
            $this->orderBy = $parser->OrderByClause();
        }

        $parser->match(TokenType::T_CLOSE_PARENTHESIS);
    }
}
