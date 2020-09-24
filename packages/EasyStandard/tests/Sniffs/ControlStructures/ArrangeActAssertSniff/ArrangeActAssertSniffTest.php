<?php

declare(strict_types=1);

namespace EonX\EasyStandard\Tests\Sniffs\ControlStructures\ArrangeActAssertSniff;

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
     * Test anonymous class without empty line succeeds.
     */
    public function testAnonymousClassWithoutEmptyLineSucceeds(): void
    {
        $fileInfo = new SmartFileInfo(__DIR__ . '/Fixture/Correct/anonymousClassWithoutEmptyLine.php.inc');
        $this->doTestCorrectFileInfo($fileInfo);
    }

    /**
     * Test anonymous class succeeds.
     */
    public function testAnonymousClassWithEmptyLineSucceeds(): void
    {
        $fileInfo = new SmartFileInfo(__DIR__ . '/Fixture/Correct/anonymousClassWithEmptyLine.php.inc');
        $this->doTestCorrectFileInfo($fileInfo);
    }

    /**
     * Tests class with no test namespace succeeds.
     */
    public function testClassWithNoTestNamespaceSucceeds(): void
    {
        $fileInfo = new SmartFileInfo(__DIR__ . '/Fixture/Correct/noTestNamespace.php.inc');
        $this->doTestCorrectFileInfo($fileInfo);
    }

    /**
     * Tests methods with correct amount of empty lines succeeds.
     */
    public function testCorrectEmptyLinesSucceeds(): void
    {
        $fileInfo = new SmartFileInfo(__DIR__ . '/Fixture/Correct/correctEmptyLines.php.inc');
        $this->doTestCorrectFileInfo($fileInfo);
    }

    /**
     * Tests method with excessive empty lines fails.
     */
    public function testExcessiveEmptyLinesFails(): void
    {
        $fileInfo = new SmartFileInfo(__DIR__ . '/Fixture/Wrong/excessiveEmptyLines.php.inc');
        $this->doTestFileInfoWithErrorCountOf($fileInfo, 1);
    }

    /**
     * Tests inline comment succeeds.
     */
    public function testInlineCommentSucceeds(): void
    {
        $fileInfo = new SmartFileInfo(__DIR__ . '/Fixture/Correct/inlineComment.php.inc');
        $this->doTestCorrectFileInfo($fileInfo);
    }

    /**
     * Tests line with comment succeeds, because comment is not an effective line.
     */
    public function testLineWithCommentSucceeds(): void
    {
        $fileInfo = new SmartFileInfo(__DIR__ . '/Fixture/Correct/oneLineWithComment.php.inc');
        $this->doTestCorrectFileInfo($fileInfo);
    }

    /**
     * Tests method with empty lines in Closure succeeds.
     */
    public function testMethodWithEmptyLinesInClosureSucceeds(): void
    {
        $fileInfo = new SmartFileInfo(__DIR__ . '/Fixture/Correct/EmptyLinesInClosure.php.inc');
        $this->doTestCorrectFileInfo($fileInfo);
    }

    /**
     * Tests method with inner curly brackets succeeds.
     */
    public function testMethodWithInnerCurlyBracketsSucceeds(): void
    {
        $fileInfo = new SmartFileInfo(__DIR__ . '/Fixture/Correct/innerCurlyBrackets.php.inc');
        $this->doTestCorrectFileInfo($fileInfo);
    }

    /**
     * Test multilevel closure succeeds.
     */
    public function testMultiLevelClosureSucceeds(): void
    {
        $fileInfo = new SmartFileInfo(__DIR__ . '/Fixture/Correct/multiLevelClosure.php.inc');
        $this->doTestCorrectFileInfo($fileInfo);
    }

    /**
     * Tests method with no empty lines fails.
     */
    public function testNoEmptyLinesFails(): void
    {
        $fileInfo = new SmartFileInfo(__DIR__ . '/Fixture/Wrong/noEmptyLines.php.inc');
        $this->doTestFileInfoWithErrorCountOf($fileInfo, 1);
    }

    /**
     * Tests no test method succeeds.
     */
    public function testNoTestMethodSucceeds(): void
    {
        $fileInfo = new SmartFileInfo(__DIR__ . '/Fixture/Correct/noTestMethod.php.inc');
        $this->doTestCorrectFileInfo($fileInfo);
    }

    /**
     * Tests one line test method succeeds.
     */
    public function testOneLineTestMethodSucceeds(): void
    {
        $fileInfo = new SmartFileInfo(__DIR__ . '/Fixture/Correct/oneLineTestMethod.php.inc');
        $this->doTestCorrectFileInfo($fileInfo);
    }

    /**
     * Test method with multiline succeeds.
     */
    public function testOneMultiLineSucceeds(): void
    {
        $fileInfo = new SmartFileInfo(__DIR__ . '/Fixture/Correct/oneMultiLine.php.inc');
        $this->doTestCorrectFileInfo($fileInfo);
    }

    protected function getCheckerClass(): string
    {
        return ArrangeActAssertSniff::class;
    }
}
