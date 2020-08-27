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
     *
     * @throws \Symplify\SmartFileSystem\Exception\FileNotFoundException
     */
    public function testExceptionWithoutMessageSucceeds(): void
    {
        $this->doTestCorrectFileInfo(
            new SmartFileInfo(self::FIXTURES_DIR . '/correct/noExceptionMessage.php.inc')
        );
    }

    /**
     * Tests hardcoded message fails.
     *
     * @throws \Symplify\SmartFileSystem\Exception\FileNotFoundException
     */
    public function testHardcodedMessageFails(): void
    {
        $this->doTestFileInfoWithErrorCountOf(
            new SmartFileInfo(self::FIXTURES_DIR . '/wrong/hardcodedMessage.php.inc'),
            1
        );
    }

    /**
     * Tests message with valid prefix succeeds.
     *
     * @throws \Symplify\SmartFileSystem\Exception\FileNotFoundException
     */
    public function testMessageWithValidPrefixSucceeds(): void
    {
        $this->doTestCorrectFileInfo(
            new SmartFileInfo(self::FIXTURES_DIR . '/correct/validPrefix.php.inc')
        );
    }

    /**
     * Tests multiline exception succeeds.
     *
     * @throws \Symplify\SmartFileSystem\Exception\FileNotFoundException
     */
    public function testMultilineExceptionSucceeds(): void
    {
        $this->doTestCorrectFileInfo(
            new SmartFileInfo(self::FIXTURES_DIR . '/correct/multilineException.php.inc')
        );
    }

    /**
     * Tests throw variable succeeds.
     *
     * @throws \Symplify\SmartFileSystem\Exception\FileNotFoundException
     */
    public function testThrowVariableSucceeds(): void
    {
        $this->doTestCorrectFileInfo(
            new SmartFileInfo(self::FIXTURES_DIR . '/correct/throwVariable.php.inc')
        );
    }

    /**
     * Tests throw exception with variable message succeeds.
     *
     * @throws \Symplify\SmartFileSystem\Exception\FileNotFoundException
     */
    public function testVariableMessageSucceeds(): void
    {
        $this->doTestCorrectFileInfo(
            new SmartFileInfo(self::FIXTURES_DIR . '/correct/variableMessage.php.inc')
        );
    }

    protected function getCheckerClass(): string
    {
        return ThrowExceptionMessageSniff::class;
    }

    protected function getCheckerConfiguration(): array
    {
        return [
            'validPrefixes' => ['exceptions.'],
        ];
    }
}
