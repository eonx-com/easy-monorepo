<?php

declare(strict_types=1);

namespace EonX\EasyCore\Tests\Doctrine\ORM\Query\AST\Functions;

use Doctrine\ORM\Query\AST\InputParameter;
use Doctrine\ORM\Query\Lexer;
use Doctrine\ORM\Query\Parser;
use Doctrine\ORM\Query\SqlWalker;
use EonX\EasyCore\Doctrine\ORM\Query\AST\Functions\Contains;
use EonX\EasyCore\Tests\AbstractTestCase;
use Mockery\MockInterface;

/**
 * @covers \EonX\EasyCore\Doctrine\ORM\Query\AST\Functions\Contains
 */
final class ContainsTest extends AbstractTestCase
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
        /** @var \Doctrine\ORM\Query\SqlWalker $sqlWalker */
        $sqlWalker = $this->mock(
            SqlWalker::class,
            static function (MockInterface $mock) use ($contains, $parameter, $inputParameter, $parameterValue): void {
                $mock
                    ->shouldReceive('walkFunction')
                    ->once()
                    ->with($contains)
                    ->andReturn($parameter);

                $mock
                    ->shouldReceive('walkInputParameter')
                    ->once()
                    ->with($inputParameter)
                    ->andReturn($parameterValue);
            }
        );
        $parser = $this->mockParser($contains, $inputParameter);
        $contains->parse($parser);

        $result = $contains->getSql($sqlWalker);

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
        /** @var \Doctrine\ORM\Query\Parser $parser */
        $parser = $this->mock(
            Parser::class,
            static function (MockInterface $mock) use ($contains, $inputParameter): void {
                $mock
                    ->shouldReceive('match')
                    ->once()
                    ->with(Lexer::T_IDENTIFIER);

                $mock
                    ->shouldReceive('match')
                    ->once()
                    ->with(Lexer::T_OPEN_PARENTHESIS);

                $mock
                    ->shouldReceive('StringPrimary')
                    ->once()
                    ->withNoArgs()
                    ->andReturn($contains);

                $mock
                    ->shouldReceive('match')
                    ->once()
                    ->with(Lexer::T_COMMA);

                $mock
                    ->shouldReceive('InputParameter')
                    ->once()
                    ->withNoArgs()
                    ->andReturn($inputParameter);

                $mock
                    ->shouldReceive('match')
                    ->once()
                    ->with(Lexer::T_CLOSE_PARENTHESIS);
            }
        );

        return $parser;
    }
}
