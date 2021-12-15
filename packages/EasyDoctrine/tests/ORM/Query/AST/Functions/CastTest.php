<?php

declare(strict_types=1);

namespace EonX\EasyDoctrine\Tests\ORM\Query\AST\Functions;

use Doctrine\ORM\Query\AST\Literal;
use Doctrine\ORM\Query\AST\PathExpression;
use Doctrine\ORM\Query\Lexer;
use Doctrine\ORM\Query\Parser;
use Doctrine\ORM\Query\SqlWalker;
use EonX\EasyDoctrine\ORM\Query\AST\Functions\Cast;
use EonX\EasyDoctrine\Tests\AbstractTestCase;

/**
 * @covers \EonX\EasyDoctrine\ORM\Query\AST\Functions\Cast
 */
final class CastTest extends AbstractTestCase
{
    /**
     * @throws \Doctrine\ORM\Query\QueryException
     */
    public function testGetSqlSucceeds(): void
    {
        $path = 'some-path';
        $typeValue = 'some-value';
        $type = new Literal(Literal::STRING, $typeValue);
        $sqlWalker = $this->prophesize(SqlWalker::class);
        $sqlWalker->walkPathExpression(null)
            ->willReturn($path);
        $cast = new Cast('no-matter');
        $this->setPrivatePropertyValue($cast, 'type', $type);

        /** @var \Doctrine\ORM\Query\SqlWalker $sqlWalkerReveal */
        $sqlWalkerReveal = $sqlWalker->reveal();
        $result = $cast->getSql($sqlWalkerReveal);

        self::assertSame(\sprintf('CAST(%s AS %s)', $path, $typeValue), $result);
    }

    /**
     * @throws \Doctrine\ORM\Query\QueryException
     */
    public function testParseSucceeds(): void
    {
        $type = new Literal(Literal::STRING, 'no-matter');
        $pathExpression = new PathExpression(PathExpression::TYPE_STATE_FIELD, 'no-matter');
        $parser = $this->prophesize(Parser::class);
        $parser->match(Lexer::T_IDENTIFIER)->shouldBeCalled();
        $parser->match(Lexer::T_OPEN_PARENTHESIS)->shouldBeCalled();
        $parser->PathExpression(PathExpression::TYPE_STATE_FIELD)->willReturn($pathExpression);
        $parser->match(Lexer::T_COMMA)->shouldBeCalled();
        $parser->StringPrimary()
            ->willReturn($type);
        $parser->match(Lexer::T_CLOSE_PARENTHESIS)->shouldBeCalled();
        $cast = new Cast('no-matter');

        /** @var \Doctrine\ORM\Query\Parser $parserReveal */
        $parserReveal = $parser->reveal();
        $cast->parse($parserReveal);

        self::assertSame($type, $this->getPrivatePropertyValue($cast, 'type'));
        self::assertSame($pathExpression, $this->getPrivatePropertyValue($cast, 'expression'));
    }
}
