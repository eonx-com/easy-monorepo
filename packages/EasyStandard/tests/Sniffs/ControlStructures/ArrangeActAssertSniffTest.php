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
     *
     * @throws \Symplify\SmartFileSystem\Exception\FileNotFoundException
     */
    public function testClassWithNoTestNamespaceSucceeds(): void
    {
        $this->doTestCorrectFileInfo(new SmartFileInfo(self::FIXTURES_DIR . '/Correct/noTestNamespace.php.inc'));
    }

    /**
     * Tests methods with correct amount of empty lines succeeds.
     *
     * @throws \Symplify\SmartFileSystem\Exception\FileNotFoundException
     */
    public function testCorrectEmptyLinesSucceeds(): void
    {
        $this->doTestCorrectFileInfo(new SmartFileInfo(self::FIXTURES_DIR . '/Correct/correctEmptyLines.php.inc'));
    }

    /**
     * Tests method with excessive empty lines fails.
     *
     * @throws \Symplify\SmartFileSystem\Exception\FileNotFoundException
     */
    public function testExcessiveEmptyLinesFails(): void
    {
        $this->doTestFileInfoWithErrorCountOf(
            new SmartFileInfo(self::FIXTURES_DIR . '/Wrong/excessiveEmptyLines.php.inc'),
            1
        );
    }

    /**
     * Tests inline comment succeeds.
     *
     * @throws \Symplify\SmartFileSystem\Exception\FileNotFoundException
     */
    public function testInlineCommentSucceeds(): void
    {
        $this->doTestCorrectFileInfo(
            new SmartFileInfo(self::FIXTURES_DIR . '/Correct/inlineComment.php.inc')
        );
    }

    /**
     * Tests line with comment succeeds, because comment is not an effective line.
     *
     * @throws \Symplify\SmartFileSystem\Exception\FileNotFoundException
     */
    public function testLineWithCommentSucceeds(): void
    {
        $this->doTestCorrectFileInfo(
            new SmartFileInfo(self::FIXTURES_DIR . '/Correct/oneLineWithComment.php.inc')
        );
    }

    /**
     * Tests method with inner curly brackets succeeds.
     *
     * @throws \Symplify\SmartFileSystem\Exception\FileNotFoundException
     */
    public function testMethodWithInnerCurlyBracketsSucceeds(): void
    {
        $this->doTestCorrectFileInfo(
            new SmartFileInfo(self::FIXTURES_DIR . '/Correct/innerCurlyBrackets.php.inc')
        );
    }

    /**
     * Tests method with no empty lines fails.
     *
     * @throws \Symplify\SmartFileSystem\Exception\FileNotFoundException
     */
    public function testNoEmptyLinesFails(): void
    {
        $this->doTestFileInfoWithErrorCountOf(
            new SmartFileInfo(self::FIXTURES_DIR . '/Wrong/noEmptyLines.php.inc'),
            1
        );
    }

    /**
     * Tests no test method succeeds.
     *
     * @throws \Symplify\SmartFileSystem\Exception\FileNotFoundException
     */
    public function testNoTestMethodSucceeds(): void
    {
        $this->doTestCorrectFileInfo(
            new SmartFileInfo(self::FIXTURES_DIR . '/Correct/noTestMethod.php.inc')
        );
    }

    /**
     * Tests one line test method succeeds.
     *
     * @throws \Symplify\SmartFileSystem\Exception\FileNotFoundException
     */
    public function testOneLineTestMethodSucceeds(): void
    {
        $this->doTestCorrectFileInfo(
            new SmartFileInfo(self::FIXTURES_DIR . '/Correct/oneLineTestMethod.php.inc')
        );
    }

    /**
     * Test method with multiline succeeds.
     *
     * @throws \Symplify\SmartFileSystem\Exception\FileNotFoundException
     */
    public function testOneMultiLineSucceeds(): void
    {
        $this->doTestCorrectFileInfo(
            new SmartFileInfo(self::FIXTURES_DIR . '/Correct/oneMultiLine.php.inc')
        );
    }

    protected function getCheckerClass(): string
    {
        return ArrangeActAssertSniff::class;
    }
}
