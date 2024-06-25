<?php
declare(strict_types=1);

namespace EonX\EasyDoctrine\Tests\Unit\Common\Function;

use Doctrine\ORM\Query\AST\InputParameter;
use Doctrine\ORM\Query\Lexer;
use Doctrine\ORM\Query\Parser;
use Doctrine\ORM\Query\SqlWalker;
use EonX\EasyDoctrine\Common\Function\Contains;
use EonX\EasyDoctrine\Tests\Unit\AbstractUnitTestCase;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(Contains::class)]
final class ContainsTest extends AbstractUnitTestCase
{
    /**
     * @throws \Doctrine\ORM\Query\QueryException
     */
    public function testGetSqlSucceeds(): void
    {
        $parameter = 'test';
        $parameterValue = 'test-value';
        $contains = new Contains($parameter);
        $inputParameter = new InputParameter($parameterValue);
        $sqlWalker = $this->prophesize(SqlWalker::class);
        $sqlWalker->walkFunction($contains)
            ->willReturn($parameter);
        $sqlWalker->walkInputParameter($inputParameter)
            ->willReturn($parameterValue);
        /** @var \Doctrine\ORM\Query\SqlWalker $sqlWalkerReveal */
        $sqlWalkerReveal = $sqlWalker->reveal();
        $parser = $this->mockParser($contains, $inputParameter);
        $contains->parse($parser);

        $result = $contains->getSql($sqlWalkerReveal);

        self::assertSame(\sprintf('(%s @> %s)', $parameter, $parameterValue), $result);
    }

    /**
     * @throws \Doctrine\ORM\Query\QueryException
     */
    public function testParseSucceeds(): void
    {
        $contains = new Contains('test');
        $inputParameter = new InputParameter('test');
        $parser = $this->mockParser($contains, $inputParameter);

        $contains->parse($parser);

        $this->expectNotToPerformAssertions();
    }

    private function mockParser(Contains $contains, InputParameter $inputParameter): Parser
    {
        $parser = $this->prophesize(Parser::class);
        $parser->match(Lexer::T_IDENTIFIER)
            ->willReturn();
        $parser->match(Lexer::T_OPEN_PARENTHESIS)
            ->willReturn();
        $parser->StringPrimary()
            ->willReturn($contains);
        $parser->match(Lexer::T_COMMA)
            ->willReturn();
        $parser->InputParameter()
            ->willReturn($inputParameter);
        $parser->match(Lexer::T_CLOSE_PARENTHESIS)
            ->willReturn();

        /** @var \Doctrine\ORM\Query\Parser $parserReveal */
        $parserReveal = $parser->reveal();

        return $parserReveal;
    }
}
