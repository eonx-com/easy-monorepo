<?php
declare(strict_types=1);

namespace EonX\EasyDoctrine\Tests\Unit\Common\Function;

use Doctrine\ORM\Query\AST\Literal;
use Doctrine\ORM\Query\AST\OrderByClause;
use Doctrine\ORM\Query\AST\PathExpression;
use Doctrine\ORM\Query\Lexer;
use Doctrine\ORM\Query\Parser;
use Doctrine\ORM\Query\SqlWalker;
use EonX\EasyDoctrine\Common\Function\StringAgg;
use EonX\EasyDoctrine\Tests\Unit\AbstractUnitTestCase;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(StringAgg::class)]
final class StringAggTest extends AbstractUnitTestCase
{
    /**
     * @throws \Doctrine\ORM\Query\QueryException
     */
    public function testGetSqlSucceeds(): void
    {
        $delimiterValue = 'some-delimiter-value';
        $expressionValue = 'some-value';
        $expression = new PathExpression(PathExpression::TYPE_STATE_FIELD, 'no-matter');
        $delimiter = new Literal(Literal::STRING, 'no-matter');
        $sqlWalker = $this->prophesize(SqlWalker::class);
        $sqlWalker->walkPathExpression($expression)
            ->willReturn($expressionValue);
        $sqlWalker->walkStringPrimary($delimiter)
            ->willReturn($delimiterValue);
        /** @var \Doctrine\ORM\Query\SqlWalker $sqlWalkerReveal */
        $sqlWalkerReveal = $sqlWalker->reveal();
        $stringAgg = new StringAgg('no-matter');
        self::setPrivatePropertyValue($stringAgg, 'delimiter', $delimiter);
        self::setPrivatePropertyValue($stringAgg, 'expression', $expression);

        $result = $stringAgg->getSql($sqlWalkerReveal);

        $expectedSql = \sprintf(
            'STRING_AGG(%s%s::CHARACTER VARYING, %s%s)',
            '',
            $expressionValue,
            $delimiterValue,
            ''
        );
        self::assertSame($expectedSql, $result);
    }

    /**
     * @throws \Doctrine\ORM\Query\QueryException
     */
    public function testGetSqlSucceedsWithDistinct(): void
    {
        $delimiterValue = 'some-delimiter-value';
        $expressionValue = 'some-value';
        $expression = new PathExpression(PathExpression::TYPE_STATE_FIELD, 'no-matter');
        $delimiter = new Literal(Literal::STRING, 'no-matter');
        $sqlWalker = $this->prophesize(SqlWalker::class);
        $sqlWalker->walkPathExpression($expression)
            ->willReturn($expressionValue);
        $sqlWalker->walkStringPrimary($delimiter)
            ->willReturn($delimiterValue);
        /** @var \Doctrine\ORM\Query\SqlWalker $sqlWalkerReveal */
        $sqlWalkerReveal = $sqlWalker->reveal();
        $stringAgg = new StringAgg('no-matter');
        self::setPrivatePropertyValue($stringAgg, 'delimiter', $delimiter);
        self::setPrivatePropertyValue($stringAgg, 'expression', $expression);
        self::setPrivatePropertyValue($stringAgg, 'isDistinct', true);

        $result = $stringAgg->getSql($sqlWalkerReveal);

        $expectedSql = \sprintf(
            'STRING_AGG(%s%s::CHARACTER VARYING, %s%s)',
            'DISTINCT ',
            $expressionValue,
            $delimiterValue,
            ''
        );
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
        $expression = new PathExpression(PathExpression::TYPE_STATE_FIELD, 'no-matter');
        $delimiter = new Literal(Literal::STRING, 'no-matter');
        $sqlWalker = $this->prophesize(SqlWalker::class);
        $sqlWalker->walkPathExpression($expression)
            ->willReturn($expressionValue);
        $sqlWalker->walkStringPrimary($delimiter)
            ->willReturn($delimiterValue);
        $sqlWalker->walkOrderByClause($orderBy)
            ->willReturn($orderByValue);
        /** @var \Doctrine\ORM\Query\SqlWalker $sqlWalkerReveal */
        $sqlWalkerReveal = $sqlWalker->reveal();
        $stringAgg = new StringAgg('no-matter');
        self::setPrivatePropertyValue($stringAgg, 'delimiter', $delimiter);
        self::setPrivatePropertyValue($stringAgg, 'expression', $expression);
        self::setPrivatePropertyValue($stringAgg, 'orderBy', $orderBy);

        $result = $stringAgg->getSql($sqlWalkerReveal);

        $expectedSql = \sprintf(
            'STRING_AGG(%s%s::CHARACTER VARYING, %s%s)',
            '',
            $expressionValue,
            $delimiterValue,
            $orderByValue
        );
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
        $parser->getLexer()
            ->willReturn($lexer->reveal());
        $parser->PathExpression(PathExpression::TYPE_STATE_FIELD)->willReturn($pathExpression);
        $parser->match(Lexer::T_COMMA)->shouldBeCalled();
        $parser->StringPrimary()
            ->willReturn($delimiter);
        $parser->match(Lexer::T_CLOSE_PARENTHESIS)->shouldBeCalled();
        $stringAgg = new StringAgg('no-matter');

        /** @var \Doctrine\ORM\Query\Parser $parserReveal */
        $parserReveal = $parser->reveal();
        $stringAgg->parse($parserReveal);

        self::assertSame($delimiter, self::getPrivatePropertyValue($stringAgg, 'delimiter'));
        self::assertFalse(self::getPrivatePropertyValue($stringAgg, 'isDistinct'));
        self::assertNull(self::getPrivatePropertyValue($stringAgg, 'orderBy'));
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
        $parser->getLexer()
            ->willReturn($lexer->reveal());
        $parser->match(Lexer::T_DISTINCT)->shouldBeCalled();
        $parser->PathExpression(PathExpression::TYPE_STATE_FIELD)->willReturn($pathExpression);
        $parser->match(Lexer::T_COMMA)->shouldBeCalled();
        $parser->StringPrimary()
            ->willReturn($delimiter);
        $parser->match(Lexer::T_CLOSE_PARENTHESIS)->shouldBeCalled();
        $stringAgg = new StringAgg('no-matter');

        /** @var \Doctrine\ORM\Query\Parser $parserReveal */
        $parserReveal = $parser->reveal();
        $stringAgg->parse($parserReveal);

        self::assertTrue(self::getPrivatePropertyValue($stringAgg, 'isDistinct'));
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
        $parser->getLexer()
            ->willReturn($lexer->reveal());
        $parser->PathExpression(PathExpression::TYPE_STATE_FIELD)->willReturn($pathExpression);
        $parser->match(Lexer::T_COMMA)->shouldBeCalled();
        $parser->StringPrimary()
            ->willReturn($delimiter);
        $parser->OrderByClause()
            ->willReturn($orderBy);
        $parser->match(Lexer::T_CLOSE_PARENTHESIS)->shouldBeCalled();
        $stringAgg = new StringAgg('no-matter');

        /** @var \Doctrine\ORM\Query\Parser $parserReveal */
        $parserReveal = $parser->reveal();
        $stringAgg->parse($parserReveal);

        self::assertSame($orderBy, self::getPrivatePropertyValue($stringAgg, 'orderBy'));
    }
}
