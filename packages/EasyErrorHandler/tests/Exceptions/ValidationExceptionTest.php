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
        $exception = (new ValidationExceptionStub())->setErrors($errors);

        self::assertSame($errors, $exception->getErrors());
    }

    public function testSetErrors(): void
    {
        $errors = ['foo' => 'bar'];

        $exception = (new ValidationExceptionStub())->setErrors($errors);

        $property = $this->getPropertyAsPublic(ValidationExceptionStub::class, 'errors');
        self::assertSame($errors, $property->getValue($exception));
    }
}
