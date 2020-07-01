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
     * @return iterable<mixed>
     */
    public function provideFixtures(): iterable
    {
        yield [self::FIXTURES_DIR . '/correct/noExceptionMessage.php.inc'];
        yield [self::FIXTURES_DIR . '/wrong/hardcodedMessage.php.inc'];
        yield [self::FIXTURES_DIR . '/correct/validPrefix.php.inc'];
        yield [self::FIXTURES_DIR . '/correct/multilineException.php.inc'];
        yield [self::FIXTURES_DIR . '/correct/throwVariable.php.inc'];
        yield [self::FIXTURES_DIR . '/correct/variableMessage.php.inc'];
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
        return ThrowExceptionMessageSniff::class;
    }

    protected function getCheckerConfiguration(): array
    {
        return [
            'validPrefixes' => ['exceptions.'],
        ];
    }
}
