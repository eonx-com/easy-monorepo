<?php
declare(strict_types=1);

namespace LoyaltyCorp\EasyEntityChange\Tests\Exceptions;

use LoyaltyCorp\EasyEntityChange\Exceptions\InvalidDispatcherException;
use LoyaltyCorp\EasyEntityChange\Tests\AbstractTestCase;

/**
 * @covers \LoyaltyCorp\EasyEntityChange\Exceptions\InvalidDispatcherException
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
