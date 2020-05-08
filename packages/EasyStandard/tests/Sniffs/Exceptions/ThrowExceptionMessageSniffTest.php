<?php

declare(strict_types=1);

namespace EonX\EasyStandard\Tests\Sniffs\Exceptions;

use EonX\EasyStandard\Sniffs\Exceptions\ThrowExceptionMessageSniff;
use Symplify\EasyCodingStandardTester\Testing\AbstractCheckerTestCase;

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
        $this->doTestCorrectFile(self::FIXTURES_DIR . '/correct/noExceptionMessage.php.inc');
    }

    /**
     * Tests hardcoded message fails.
     */
    public function testHardcodedMessageFails(): void
    {
        $this->doTestWrongFile(self::FIXTURES_DIR . '/wrong/hardcodedMessage.php.inc');
    }

    /**
     * Tests message with valid prefix succeeds.
     */
    public function testMessageWithValidPrefixSucceeds(): void
    {
        $this->doTestCorrectFile(self::FIXTURES_DIR . '/correct/validPrefix.php.inc');
    }

    /**
     * Tests multiline exception succeeds.
     */
    public function testMultilineExceptionSucceeds(): void
    {
        $this->doTestCorrectFile(self::FIXTURES_DIR . '/correct/multilineException.php.inc');
    }

    /**
     * Tests throw variable succeeds.
     */
    public function testThrowVariableSucceeds(): void
    {
        $this->doTestCorrectFile(self::FIXTURES_DIR . '/correct/throwVariable.php.inc');
    }

    /**
     * Tests throw exception with variable message succeeds.
     */
    public function testVariableMessageSucceeds(): void
    {
        $this->doTestCorrectFile(self::FIXTURES_DIR . '/correct/variableMessage.php.inc');
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
