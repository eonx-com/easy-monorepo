<?php
declare(strict_types=1);

namespace EonX\EasyDoctrine\Tests\ORM\Query\AST\Functions;

use Doctrine\ORM\Query\AST\Literal;
use Doctrine\ORM\Query\AST\OrderByClause;
use Doctrine\ORM\Query\AST\PathExpression;
use Doctrine\ORM\Query\Lexer;
use Doctrine\ORM\Query\Parser;
use Doctrine\ORM\Query\SqlWalker;
use EonX\EasyDoctrine\ORM\Query\AST\Functions\StringAgg;
use EonX\EasyDoctrine\Tests\AbstractTestCase;

/**
 * @covers \EonX\EasyDoctrine\ORM\Query\AST\Functions\StringAgg
 */
final class StringAggTest extends AbstractTestCase
{
    /**
     * @throws \Doctrine\ORM\Query\QueryException
     */
    public function testGetSqlSucceeds(): void
    {
        $delimiterValue = 'some-delimiter-value';
        $expressionValue = 'some-value';
        $expectedSql = \sprintf(
            'STRING_AGG(%s%s::CHARACTER VARYING, %s%s)',
            '',
            $expressionValue,
            $delimiterValue,
            ''
        );
        $sqlWalker = $this->prophesize(SqlWalker::class);
        $sqlWalker->walkPathExpression(null)->willReturn($expressionValue);
        $sqlWalker->walkStringPrimary(null)->willReturn($delimiterValue);
        $stringAgg = new StringAgg('no-matter');

        $result = $stringAgg->getSql($sqlWalker->reveal());

        self::assertSame($expectedSql, $result);
    }

    /**
     * @throws \Doctrine\ORM\Query\QueryException
     */
    public function testGetSqlSucceedsWithDistinct(): void
    {
        $delimiterValue = 'some-delimiter-value';
        $expressionValue = 'some-value';
        $expectedSql = \sprintf(
            'STRING_AGG(%s%s::CHARACTER VARYING, %s%s)',
            'DISTINCT ',
            $expressionValue,
            $delimiterValue,
            ''
        );
        $sqlWalker = $this->prophesize(SqlWalker::class);
        $sqlWalker->walkPathExpression(null)->willReturn($expressionValue);
        $sqlWalker->walkStringPrimary(null)->willReturn($delimiterValue);
        $stringAgg = new StringAgg('no-matter');
        $this->setPrivatePropertyValue($stringAgg, 'isDistinct', true);

        $result = $stringAgg->getSql($sqlWalker->reveal());

        self::assertSame($expectedSql, $result);
    }

    /**
     * @throws \Doctrine\ORM\Query\QueryException
     */
    public function testGetSqlSucceedsWithOrderBy(): void
    {
        $delimiterValue = 'some-delimiter-value';
        $expressionValue = 'some-value';
        $orderByValue = 'some-value';
        $orderBy = new OrderByClause([]);
        $expectedSql = \sprintf(
            'STRING_AGG(%s%s::CHARACTER VARYING, %s%s)',
            '',
            $expressionValue,
            $delimiterValue,
            $orderByValue
        );
        $sqlWalker = $this->prophesize(SqlWalker::class);
        $sqlWalker->walkPathExpression(null)->willReturn($expressionValue);
        $sqlWalker->walkStringPrimary(null)->willReturn($delimiterValue);
        $sqlWalker->walkOrderByClause($orderBy)->willReturn($orderByValue);
        $stringAgg = new StringAgg('no-matter');
        $this->setPrivatePropertyValue($stringAgg, 'orderBy', $orderBy);

        $result = $stringAgg->getSql($sqlWalker->reveal());

        self::assertSame($expectedSql, $result);
    }

    /**
     * @throws \Doctrine\ORM\Query\QueryException
     */
    public function testParseSucceeds(): void
    {
        $delimiter = new Literal(Literal::STRING, 'no-matter');
        $pathExpression = new PathExpression(PathExpression::TYPE_STATE_FIELD, 'no-matter');
        $lexer = $this->prophesize(Lexer::class);
        $lexer->isNextToken(Lexer::T_DISTINCT)->willReturn(false);
        $lexer->isNextToken(Lexer::T_ORDER)->willReturn(false);
        $parser = $this->prophesize(Parser::class);
        $parser->match(Lexer::T_IDENTIFIER)->shouldBeCalled();
        $parser->match(Lexer::T_OPEN_PARENTHESIS)->shouldBeCalled();
        $parser->getLexer()->willReturn($lexer->reveal());
        $parser->PathExpression(PathExpression::TYPE_STATE_FIELD)->willReturn($pathExpression);
        $parser->match(Lexer::T_COMMA)->shouldBeCalled();
        $parser->StringPrimary()->willReturn($delimiter);
        $parser->match(Lexer::T_CLOSE_PARENTHESIS)->shouldBeCalled();
        $stringAgg = new StringAgg('no-matter');

        $stringAgg->parse($parser->reveal());

        self::assertSame($delimiter, $this->getPrivatePropertyValue($stringAgg, 'delimiter'));
        self::assertFalse($this->getPrivatePropertyValue($stringAgg, 'isDistinct'));
        self::assertNull($this->getPrivatePropertyValue($stringAgg, 'orderBy'));
    }

    /**
     * @throws \Doctrine\ORM\Query\QueryException
     */
    public function testParseSucceedsWithDistinct(): void
    {
        $delimiter = new Literal(Literal::STRING, 'no-matter');
        $pathExpression = new PathExpression(PathExpression::TYPE_STATE_FIELD, 'no-matter');
        $lexer = $this->prophesize(Lexer::class);
        $lexer->isNextToken(Lexer::T_DISTINCT)->willReturn(true);
        $lexer->isNextToken(Lexer::T_ORDER)->willReturn(false);
        $parser = $this->prophesize(Parser::class);
        $parser->match(Lexer::T_IDENTIFIER)->shouldBeCalled();
        $parser->match(Lexer::T_OPEN_PARENTHESIS)->shouldBeCalled();
        $parser->getLexer()->willReturn($lexer->reveal());
        $parser->match(Lexer::T_DISTINCT)->shouldBeCalled();
        $parser->PathExpression(PathExpression::TYPE_STATE_FIELD)->willReturn($pathExpression);
        $parser->match(Lexer::T_COMMA)->shouldBeCalled();
        $parser->StringPrimary()->willReturn($delimiter);
        $parser->match(Lexer::T_CLOSE_PARENTHESIS)->shouldBeCalled();
        $stringAgg = new StringAgg('no-matter');

        $stringAgg->parse($parser->reveal());

        self::assertTrue($this->getPrivatePropertyValue($stringAgg, 'isDistinct'));
    }

    /**
     * @throws \Doctrine\ORM\Query\QueryException
     */
    public function testParseSucceedsWithOrderBy(): void
    {
        $orderBy = new OrderByClause([]);
        $delimiter = new Literal(Literal::STRING, 'no-matter');
        $pathExpression = new PathExpression(PathExpression::TYPE_STATE_FIELD, 'no-matter');
        $lexer = $this->prophesize(Lexer::class);
        $lexer->isNextToken(Lexer::T_DISTINCT)->willReturn(false);
        $lexer->isNextToken(Lexer::T_ORDER)->willReturn(true);
        $parser = $this->prophesize(Parser::class);
        $parser->match(Lexer::T_IDENTIFIER)->shouldBeCalled();
        $parser->match(Lexer::T_OPEN_PARENTHESIS)->shouldBeCalled();
        $parser->getLexer()->willReturn($lexer->reveal());
        $parser->PathExpression(PathExpression::TYPE_STATE_FIELD)->willReturn($pathExpression);
        $parser->match(Lexer::T_COMMA)->shouldBeCalled();
        $parser->StringPrimary()->willReturn($delimiter);
        $parser->OrderByClause()->willReturn($orderBy);
        $parser->match(Lexer::T_CLOSE_PARENTHESIS)->shouldBeCalled();
        $stringAgg = new StringAgg('no-matter');

        $stringAgg->parse($parser->reveal());

        self::assertSame($orderBy, $this->getPrivatePropertyValue($stringAgg, 'orderBy'));
    }
}
