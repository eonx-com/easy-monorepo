<?php
declare(strict_types=1);

namespace EonX\EasyBankFiles\Tests\Parsers;

use EonX\EasyBankFiles\Parsers\Error;

/**
 * @covers \EonX\EasyBankFiles\Parsers\Error
 */
final class ErrorTest extends TestCase
{
    /**
     * Result should return data as expected.
     */
    public function testGetDataAsExpected(): void
    {
        $data = [
            'line' => 'line',
            'lineNumber' => 23,
        ];

        $error = new Error($data);

        self::assertSame($data['line'], $error->getLine());
        self::assertSame($data['lineNumber'], $error->getLineNumber());
    }
}
