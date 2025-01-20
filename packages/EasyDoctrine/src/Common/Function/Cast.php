<?php
declare(strict_types=1);

namespace EonX\EasyDoctrine\Common\Function;

use Doctrine\ORM\Query\AST\Functions\FunctionNode;
use Doctrine\ORM\Query\AST\Literal;
use Doctrine\ORM\Query\AST\PathExpression;
use Doctrine\ORM\Query\Parser;
use Doctrine\ORM\Query\SqlWalker;
use Doctrine\ORM\Query\TokenType;

final class Cast extends FunctionNode
{
    private PathExpression $expression;

    private Literal $type;

    /**
     * @throws \Doctrine\ORM\Query\QueryException
     */
    public function getSql(SqlWalker $sqlWalker): string
    {
        /** @var string $type */
        $type = $this->type->value;

        return \sprintf(
            'CAST(%s AS %s)',
            $sqlWalker->walkPathExpression($this->expression),
            $type
        );
    }

    /**
     * @throws \Doctrine\ORM\Query\QueryException
     */
    public function parse(Parser $parser): void
    {
        $parser->match(TokenType::T_IDENTIFIER);
        $parser->match(TokenType::T_OPEN_PARENTHESIS);
        $this->expression = $parser->PathExpression(PathExpression::TYPE_STATE_FIELD);
        $parser->match(TokenType::T_COMMA);
        /** @var \Doctrine\ORM\Query\AST\Literal $type */
        $type = $parser->StringPrimary();
        $this->type = $type;
        $parser->match(TokenType::T_CLOSE_PARENTHESIS);
    }
}
