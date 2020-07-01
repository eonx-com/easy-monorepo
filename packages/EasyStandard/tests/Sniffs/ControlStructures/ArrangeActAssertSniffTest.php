<?php

declare(strict_types=1);

namespace EonX\EasyStandard\Tests\Sniffs\ControlStructures;

use EonX\EasyStandard\Sniffs\ControlStructures\ArrangeActAssertSniff;
use Symplify\EasyCodingStandardTester\Testing\AbstractCheckerTestCase;
use Symplify\SmartFileSystem\SmartFileInfo;

/**
 * @covers \EonX\EasyStandard\Sniffs\ControlStructures\ArrangeActAssertSniff
 *
 * @internal
 */
final class ArrangeActAssertSniffTest extends AbstractCheckerTestCase
{
    /**
     * @var string
     */
    private const FIXTURES_DIR = __DIR__ . '/../../fixtures/Sniffs/ControlStructures/ArrangeActAssertSniff';

    /**
     * @return iterable<mixed>
     */
    public function provideFixtures(): iterable
    {
        yield [self::FIXTURES_DIR . '/Correct/noTestNamespace.php.inc'];
        yield [self::FIXTURES_DIR . '/Correct/correctEmptyLines.php.inc'];
        yield [self::FIXTURES_DIR . '/Wrong/excessiveEmptyLines.php.inc'];
        yield [self::FIXTURES_DIR . '/Correct/inlineComment.php.inc'];
        yield [self::FIXTURES_DIR . '/Correct/oneLineWithComment.php.inc'];
        yield [self::FIXTURES_DIR . '/Correct/innerCurlyBrackets.php.inc'];
        yield [self::FIXTURES_DIR . '/Wrong/noEmptyLines.php.inc'];
        yield [self::FIXTURES_DIR . '/Correct/noTestMethod.php.inc'];
        yield [self::FIXTURES_DIR . '/Correct/oneLineTestMethod.php.inc'];
        yield [self::FIXTURES_DIR . '/Correct/oneMultiLine.php.inc'];
    }

    /**
     * @dataProvider provideFixtures
     */
    public function testSniffs(string $file): void
    {
        $this->doTestFileInfo(new SmartFileInfo($file));
    }

    protected function getCheckerClass(): string
    {
        return ArrangeActAssertSniff::class;
    }
}
