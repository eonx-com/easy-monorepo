<?php

declare(strict_types=1);

namespace EonX\EasyStandard\Tests\Sniffs\ControlStructures;

use EonX\EasyStandard\Sniffs\ControlStructures\ExceptionAssertionsSniff;
use Symplify\EasyCodingStandardTester\Testing\AbstractCheckerTestCase;
use Symplify\SmartFileSystem\SmartFileInfo;

/**
 * @covers \EonX\EasyStandard\Sniffs\ControlStructures\ExceptionAssertionsSniff
 *
 * @internal
 */
final class ExceptionAssertionsSniffTest extends AbstractCheckerTestCase
{
    /**
     * @var string
     */
    private const FIXTURES_DIR = __DIR__ . '/../../fixtures/Sniffs/ControlStructures/ExceptionAssertionsSniff';

    /**
     * @return iterable<mixed>
     */
    public function provideFixtures(): iterable
    {
        yield [self::FIXTURES_DIR . '/Correct/HaveAllNecessaryAssertions.php', 0];
        yield [self::FIXTURES_DIR . '/Correct/NonTranslatableExceptionTest.php.inc', 0];
        yield [self::FIXTURES_DIR . '/Correct/NoSafeCallMethod.php', 0];
        yield [self::FIXTURES_DIR . '/Wrong/MissingExceptionMessageAssertion.php', 1];
        yield [self::FIXTURES_DIR . '/Wrong/MissingExceptionMessageParamsAssertion.php', 1];
        yield [self::FIXTURES_DIR . '/Wrong/MissingUserMessageAssertion.php', 1];
        yield [self::FIXTURES_DIR . '/Wrong/MissingUserMessageParamsAssertion.php', 1];
    }

    /**
     * @dataProvider provideFixtures
     */
    public function testSniff(string $file, int $expectedErrorCount): void
    {
        $this->doTestFileInfoWithErrorCountOf(new SmartFileInfo($file), $expectedErrorCount);
    }

    protected function getCheckerClass(): string
    {
        return ExceptionAssertionsSniff::class;
    }
}
