<?php

declare(strict_types=1);

namespace EonX\EasyBankFiles\Tests\Parsers\Nai\Results;

use EonX\EasyBankFiles\Parsers\Nai\Results\Error;
use EonX\EasyBankFiles\Tests\Parsers\TestCase;

/**
 * @covers \EonX\EasyBankFiles\Parsers\Nai\Results\Error
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
