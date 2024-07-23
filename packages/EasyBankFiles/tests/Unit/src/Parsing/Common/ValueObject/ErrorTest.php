<?php
declare(strict_types=1);

namespace EonX\EasyBankFiles\Tests\Unit\Parsing\Common\ValueObject;

use EonX\EasyBankFiles\Parsing\Common\ValueObject\Error;
use EonX\EasyBankFiles\Tests\Unit\AbstractUnitTestCase;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(Error::class)]
final class ErrorTest extends AbstractUnitTestCase
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
