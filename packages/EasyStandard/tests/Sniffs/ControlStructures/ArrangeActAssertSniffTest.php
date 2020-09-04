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
     * Tests class with no test namespace succeeds.
     */
    public function testClassWithNoTestNamespaceSucceeds(): void
    {
        $fileInfo = new SmartFileInfo(self::FIXTURES_DIR . '/Correct/noTestNamespace.php.inc');
        $this->doTestCorrectFileInfo($fileInfo);
    }

    /**
     * Tests methods with correct amount of empty lines succeeds.
     */
    public function testCorrectEmptyLinesSucceeds(): void
    {
        $fileInfo = new SmartFileInfo(self::FIXTURES_DIR . '/Correct/correctEmptyLines.php.inc');
        $this->doTestCorrectFileInfo($fileInfo);
    }

    /**
     * Tests method with excessive empty lines fails.
     */
    public function testExcessiveEmptyLinesFails(): void
    {
        $fileInfo = new SmartFileInfo(self::FIXTURES_DIR . '/Wrong/excessiveEmptyLines.php.inc');
        $this->doTestFileInfoWithErrorCountOf($fileInfo, 1);
    }

    /**
     * Tests inline comment succeeds.
     */
    public function testInlineCommentSucceeds(): void
    {
        $fileInfo = new SmartFileInfo(self::FIXTURES_DIR . '/Correct/inlineComment.php.inc');
        $this->doTestCorrectFileInfo($fileInfo);
    }

    /**
     * Tests line with comment succeeds, because comment is not an effective line.
     */
    public function testLineWithCommentSucceeds(): void
    {
        $fileInfo = new SmartFileInfo(self::FIXTURES_DIR . '/Correct/oneLineWithComment.php.inc');
        $this->doTestCorrectFileInfo($fileInfo);
    }

    /**
     * Tests method with inner curly brackets succeeds.
     */
    public function testMethodWithInnerCurlyBracketsSucceeds(): void
    {
        $fileInfo = new SmartFileInfo(self::FIXTURES_DIR . '/Correct/innerCurlyBrackets.php.inc');
        $this->doTestCorrectFileInfo($fileInfo);
    }

    /**
     * Tests method with no empty lines fails.
     */
    public function testNoEmptyLinesFails(): void
    {
        $fileInfo = new SmartFileInfo(self::FIXTURES_DIR . '/Wrong/noEmptyLines.php.inc');
        $this->doTestFileInfoWithErrorCountOf($fileInfo, 1);
    }

    /**
     * Tests no test method succeeds.
     */
    public function testNoTestMethodSucceeds(): void
    {
        $fileInfo = new SmartFileInfo(self::FIXTURES_DIR . '/Correct/noTestMethod.php.inc');
        $this->doTestCorrectFileInfo($fileInfo);
    }

    /**
     * Tests one line test method succeeds.
     */
    public function testOneLineTestMethodSucceeds(): void
    {
        $fileInfo = new SmartFileInfo(self::FIXTURES_DIR . '/Correct/oneLineTestMethod.php.inc');
        $this->doTestCorrectFileInfo($fileInfo);
    }

    /**
     * Test method with multiline succeeds.
     */
    public function testOneMultiLineSucceeds(): void
    {
        $fileInfo = new SmartFileInfo(self::FIXTURES_DIR . '/Correct/oneMultiLine.php.inc');
        $this->doTestCorrectFileInfo($fileInfo);
    }

    protected function getCheckerClass(): string
    {
        return ArrangeActAssertSniff::class;
    }
}
