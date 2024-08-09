<?php
declare(strict_types=1);

namespace EonX\EasyDoctrine\Common\Function;

use Doctrine\ORM\Query\AST\Functions\FunctionNode;
use Doctrine\ORM\Query\AST\InputParameter;
use Doctrine\ORM\Query\AST\Node;
use Doctrine\ORM\Query\Parser;
use Doctrine\ORM\Query\SqlWalker;
use Doctrine\ORM\Query\TokenType;

final class Contains extends FunctionNode
{
    private InputParameter $inputParameter;

    private Node $node;

    /**
     * @throws \Doctrine\ORM\Query\AST\ASTException
     */
    public function getSql(SqlWalker $sqlWalker): string
    {
        return \sprintf(
            '(%s @> %s)',
            $this->node->dispatch($sqlWalker),
            $sqlWalker->walkInputParameter($this->inputParameter)
        );
    }

    /**
     * @throws \Doctrine\ORM\Query\QueryException
     */
    public function parse(Parser $parser): void
    {
        $parser->match(TokenType::T_IDENTIFIER);
        $parser->match(TokenType::T_OPEN_PARENTHESIS);
        $this->node = $parser->StringPrimary();
        $parser->match(TokenType::T_COMMA);
        $this->inputParameter = $parser->InputParameter();
        $parser->match(TokenType::T_CLOSE_PARENTHESIS);
    }
}
