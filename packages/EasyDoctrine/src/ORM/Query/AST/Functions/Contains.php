<?php

declare(strict_types=1);

namespace EonX\EasyDoctrine\ORM\Query\AST\Functions;

use Doctrine\ORM\Query\AST\Functions\FunctionNode;
use Doctrine\ORM\Query\Lexer;
use Doctrine\ORM\Query\Parser;
use Doctrine\ORM\Query\SqlWalker;

final class Contains extends FunctionNode
{
    /**
     * @var \Doctrine\ORM\Query\AST\InputParameter
     */
    private $inputParameter;

    /**
     * @var \Doctrine\ORM\Query\AST\Node
     */
    private $node;

    /**
     * @throws \Doctrine\ORM\Query\AST\ASTException
     */
    public function getSql(SqlWalker $sqlWalker): string
    {
        return \sprintf(
            '(%s @> %s)',
            $this->node->dispatch($sqlWalker),
            $sqlWalker->walkInputParameter($this->inputParameter),
        );
    }

    /**
     * @throws \Doctrine\ORM\Query\QueryException
     */
    public function parse(Parser $parser): void
    {
        $parser->match(Lexer::T_IDENTIFIER);
        $parser->match(Lexer::T_OPEN_PARENTHESIS);
        $this->node = $parser->StringPrimary();
        $parser->match(Lexer::T_COMMA);
        $this->inputParameter = $parser->InputParameter();
        $parser->match(Lexer::T_CLOSE_PARENTHESIS);
    }
}
