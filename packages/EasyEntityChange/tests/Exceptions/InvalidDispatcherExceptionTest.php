<?php
declare(strict_types=1);

namespace EonX\EasyEntityChange\Tests\Exceptions;

use EonX\EasyEntityChange\Exceptions\InvalidDispatcherException;
use EonX\EasyEntityChange\Tests\AbstractTestCase;

/**
 * @covers \EonX\EasyEntityChange\Exceptions\InvalidDispatcherException
 */
class InvalidDispatcherExceptionTest extends AbstractTestCase
{
    /**
     * Test exception have a code.
     *
     * @return void
     */
    public function testExceptionCodes(): void
    {
        $exception = new InvalidDispatcherException();

        self::assertSame(1210, $exception->getErrorCode());
        self::assertSame(1, $exception->getErrorSubCode());
    }
}
