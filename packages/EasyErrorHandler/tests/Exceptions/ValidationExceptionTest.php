<?php
declare(strict_types=1);

namespace EonX\EasyErrorHandler\Tests\Exceptions;

use EonX\EasyErrorHandler\Tests\AbstractTestCase;
use EonX\EasyErrorHandler\Tests\Stubs\ValidationExceptionStub;

final class ValidationExceptionTest extends AbstractTestCase
{
    public function testGetErrors(): void
    {
        $errors = ['foo' => 'bar'];
        $exception = new ValidationExceptionStub();
        self::setPrivatePropertyValue($exception, 'errors', $errors);

        $result = $exception->getErrors();

        self::assertSame($errors, $result);
    }

    public function testSetErrors(): void
    {
        $errors = ['foo' => 'bar'];
        $exception = new ValidationExceptionStub();

        $result = $exception->setErrors($errors);

        self::assertSame($exception, $result);
        self::assertSame($errors, self::getPrivatePropertyValue($result, 'errors'));
    }
}
