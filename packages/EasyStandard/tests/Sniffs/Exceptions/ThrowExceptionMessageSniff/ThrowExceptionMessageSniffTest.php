<?php

declare(strict_types=1);

namespace EonX\EasyStandard\Tests\Sniffs\Exceptions\ThrowExceptionMessageSniff;

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
     * Tests exception without message succeeds.
     *
     * @throws \Symplify\SmartFileSystem\Exception\FileNotFoundException
     */
    public function testExceptionWithoutMessageSucceeds(): void
    {
        $fileInfo = new SmartFileInfo(__DIR__ . '/Fixtures/correct/noExceptionMessage.php.inc');
        $this->doTestCorrectFileInfo($fileInfo);
    }

    /**
     * Tests hardcoded message fails.
     *
     * @throws \Symplify\SmartFileSystem\Exception\FileNotFoundException
     */
    public function testHardcodedMessageFails(): void
    {
        $wrongFileInfo = new SmartFileInfo(__DIR__ . '/Fixtures/wrong/hardcodedMessage.php.inc');
        $this->doTestFileInfoWithErrorCountOf($wrongFileInfo, 1);
    }

    /**
     * Tests message with valid prefix succeeds.
     *
     * @throws \Symplify\SmartFileSystem\Exception\FileNotFoundException
     */
    public function testMessageWithValidPrefixSucceeds(): void
    {
        $fileInfo = new SmartFileInfo(__DIR__ . '/Fixtures/correct/validPrefix.php.inc');
        $this->doTestCorrectFileInfo($fileInfo);
    }

    /**
     * Tests multiline exception succeeds.
     *
     * @throws \Symplify\SmartFileSystem\Exception\FileNotFoundException
     */
    public function testMultilineExceptionSucceeds(): void
    {
        $fileInfo = new SmartFileInfo(__DIR__ . '/Fixtures/correct/multilineException.php.inc');
        $this->doTestCorrectFileInfo($fileInfo);
    }

    /**
     * Tests throw variable succeeds.
     *
     * @throws \Symplify\SmartFileSystem\Exception\FileNotFoundException
     */
    public function testThrowVariableSucceeds(): void
    {
        $fileInfo = new SmartFileInfo(__DIR__ . '/Fixtures/correct/throwVariable.php.inc');
        $this->doTestCorrectFileInfo($fileInfo);
    }

    /**
     * Tests throw exception with variable message succeeds.
     *
     * @throws \Symplify\SmartFileSystem\Exception\FileNotFoundException
     */
    public function testVariableMessageSucceeds(): void
    {
        $fileInfo = new SmartFileInfo(__DIR__ . '/Fixtures/correct/variableMessage.php.inc');
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
