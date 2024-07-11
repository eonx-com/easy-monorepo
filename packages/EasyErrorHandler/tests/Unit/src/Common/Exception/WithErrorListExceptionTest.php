<?php
declare(strict_types=1);

namespace EonX\EasyErrorHandler\Tests\Unit\Common\Exception;

use EonX\EasyErrorHandler\Tests\Stub\Exception\WithErrorListExceptionStub;
use EonX\EasyErrorHandler\Tests\Unit\AbstractUnitTestCase;

final class WithErrorListExceptionTest extends AbstractUnitTestCase
{
    public function testGetErrors(): void
    {
        $errors = ['foo' => 'bar'];
        $exception = new WithErrorListExceptionStub();
        self::setPrivatePropertyValue($exception, 'errors', $errors);

        $result = $exception->getErrors();

        self::assertSame($errors, $result);
    }

    public function testSetErrors(): void
    {
        $errors = ['foo' => 'bar'];
        $exception = new WithErrorListExceptionStub();

        $result = $exception->setErrors($errors);

        self::assertSame($exception, $result);
        self::assertSame($errors, self::getPrivatePropertyValue($result, 'errors'));
    }
}
