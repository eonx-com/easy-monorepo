<?php

declare(strict_types=1);

namespace EonX\EasyStandard\Tests\Sniffs\Exceptions;

use EonX\EasyStandard\Sniffs\Exceptions\ThrowExceptionMessageSniff;
use Symplify\EasyCodingStandardTester\Testing\AbstractCheckerTestCase;
use Symplify\SmartFileSystem\SmartFileInfo;

/**
 * @covers \EonX\EasyStandard\Sniffs\Exceptions\ThrowExceptionMessageSniff
 *
 * @internal
 */
final class ThrowExceptionMessageSniffTest extends AbstractCheckerTestCase
{
    /**
     * @var string
     */
    private const FIXTURES_DIR = __DIR__ . '/../../fixtures/Sniffs/Exceptions/ThrowExceptionMessageSniff';

    /**
     * Tests exception without message succeeds.
     */
    public function testExceptionWithoutMessageSucceeds(): void
    {
        $fileInfo = new SmartFileInfo(self::FIXTURES_DIR . '/correct/noExceptionMessage.php.inc');
        $this->doTestCorrectFileInfo($fileInfo);
    }

    /**
     * Tests hardcoded message fails.
     */
    public function testHardcodedMessageFails(): void
    {
        $wrongFileInfo = new SmartFileInfo(self::FIXTURES_DIR . '/wrong/hardcodedMessage.php.inc');
        $this->doTestFileInfoWithErrorCountOf($wrongFileInfo, 1);
    }

    /**
     * Tests message with valid prefix succeeds.
     */
    public function testMessageWithValidPrefixSucceeds(): void
    {
        $fileInfo = new SmartFileInfo(self::FIXTURES_DIR . '/correct/validPrefix.php.inc');
        $this->doTestCorrectFileInfo($fileInfo);
    }

    /**
     * Tests multiline exception succeeds.
     */
    public function testMultilineExceptionSucceeds(): void
    {
        $fileInfo = new SmartFileInfo(self::FIXTURES_DIR . '/correct/multilineException.php.inc');
        $this->doTestCorrectFileInfo($fileInfo);
    }

    /**
     * Tests throw variable succeeds.
     */
    public function testThrowVariableSucceeds(): void
    {
        $fileInfo = new SmartFileInfo(self::FIXTURES_DIR . '/correct/throwVariable.php.inc');
        $this->doTestCorrectFileInfo($fileInfo);
    }

    /**
     * Tests throw exception with variable message succeeds.
     */
    public function testVariableMessageSucceeds(): void
    {
        $fileInfo = new SmartFileInfo(self::FIXTURES_DIR . '/correct/variableMessage.php.inc');
        $this->doTestCorrectFileInfo($fileInfo);
    }

    protected function getCheckerClass(): string
    {
        return ThrowExceptionMessageSniff::class;
    }

    protected function getCheckerConfiguration(): array
    {
        return [
            'validPrefixes' => ['exceptions'],
        ];
    }
}
