<?php

declare(strict_types=1);

namespace EonX\EasyDoctrine\ORM\Query\AST\Functions;

use Doctrine\ORM\Query\AST\Functions\FunctionNode;
use Doctrine\ORM\Query\AST\Literal;
use Doctrine\ORM\Query\AST\PathExpression;
use Doctrine\ORM\Query\Lexer;
use Doctrine\ORM\Query\Parser;
use Doctrine\ORM\Query\SqlWalker;

final class Cast extends FunctionNode
{
    private PathExpression $expression;

    private Literal $type;

    /**
     * @throws \Doctrine\ORM\Query\QueryException
     */
    public function getSql(SqlWalker $sqlWalker): string
    {
        return \sprintf(
            'CAST(%s AS %s)',
            $sqlWalker->walkPathExpression($this->expression),
            $this->type->value
        );
    }

    /**
     * @throws \Doctrine\ORM\Query\QueryException
     */
    public function parse(Parser $parser): void
    {
        $parser->match(Lexer::T_IDENTIFIER);
        $parser->match(Lexer::T_OPEN_PARENTHESIS);
        $this->expression = $parser->PathExpression(PathExpression::TYPE_STATE_FIELD);
        $parser->match(Lexer::T_COMMA);
        /** @var \Doctrine\ORM\Query\AST\Literal $type */
        $type = $parser->StringPrimary();
        $this->type = $type;
        $parser->match(Lexer::T_CLOSE_PARENTHESIS);
    }
}
