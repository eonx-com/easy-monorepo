<?php
declare(strict_types=1);

namespace EonX\EasyStandard\Tests\Sniffs\ControlStructures;

use EonX\EasyStandard\Sniffs\ControlStructures\ArrangeActAssertSniff;
use Symplify\EasyCodingStandardTester\Testing\AbstractCheckerTestCase;

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
     * @return void
     */
    public function testClassWithNoTestNamespaceSucceeds(): void
    {
        $this->doTestCorrectFile(self::FIXTURES_DIR . '/Correct/noTestNamespace.php.inc');
    }

    /**
     * Tests methods with correct amount of empty lines succeeds.
     *
     * @return void
     */
    public function testCorrectEmptyLinesSucceeds(): void
    {
        $this->doTestCorrectFile(self::FIXTURES_DIR . '/Correct/correctEmptyLines.php.inc');
    }

    /**
     * Tests method with excessive empty lines fails.
     *
     * @return void
     */
    public function testExcessiveEmptyLinesFails(): void
    {
        $this->doTestWrongFile(self::FIXTURES_DIR . '/Wrong/excessiveEmptyLines.php.inc');
    }

    /**
     * Tests inline comment succeeds.
     *
     * @return void
     */
    public function testInlineCommentSucceeds(): void
    {
        $this->doTestCorrectFile(self::FIXTURES_DIR . '/Correct/inlineComment.php.inc');
    }

    /**
     * Tests line with comment succeeds, because comment is not an effective line.
     *
     * @return void
     */
    public function testLineWithCommentSucceeds(): void
    {
        $this->doTestCorrectFile(self::FIXTURES_DIR . '/Correct/oneLineWithComment.php.inc');
    }

    /**
     * Tests method with inner curly brackets succeeds.
     *
     * @return void
     */
    public function testMethodWithInnerCurlyBracketsSucceeds(): void
    {
        $this->doTestCorrectFile(self::FIXTURES_DIR . '/Correct/innerCurlyBrackets.php.inc');
    }

    /**
     * Tests method with no empty lines fails.
     *
     * @return void
     */
    public function testNoEmptyLinesFails(): void
    {
        $this->doTestWrongFile(self::FIXTURES_DIR . '/Wrong/noEmptyLines.php.inc');
    }

    /**
     * Tests no test method succeeds.
     *
     * @return void
     */
    public function testNoTestMethodSucceeds(): void
    {
        $this->doTestCorrectFile(self::FIXTURES_DIR . '/Correct/noTestMethod.php.inc');
    }

    /**
     * Tests one line test method succeeds.
     *
     * @return void
     */
    public function testOneLineTestMethodSucceeds(): void
    {
        $this->doTestCorrectFile(self::FIXTURES_DIR . '/Correct/oneLineTestMethod.php.inc');
    }

    /**
     * Test method with multiline succeeds.
     *
     * @return void
     */
    public function testOneMultiLineSucceeds(): void
    {
        $this->doTestCorrectFile(self::FIXTURES_DIR . '/Correct/oneMultiLine.php.inc');
    }

    /**
     * @return string
     */
    protected function getCheckerClass(): string
    {
        return ArrangeActAssertSniff::class;
    }
}
