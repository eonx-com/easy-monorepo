<?php
declare(strict_types=1);

namespace EonX\EasyErrorHandler\Tests\Unit\Common\ErrorHandler;

use EonX\EasyErrorHandler\Common\ErrorHandler\ErrorHandlerAwareTrait;
use EonX\EasyErrorHandler\Common\ErrorHandler\ErrorHandlerInterface;
use EonX\EasyErrorHandler\Tests\Unit\AbstractUnitTestCase;

final class ErrorHandlerAwareTraitTest extends AbstractUnitTestCase
{
    public function testSetErrorHandlerSucceeds(): void
    {
        $abstractClass = new class() {
            use ErrorHandlerAwareTrait;
        };
        $errorHandler = $this->createMock(ErrorHandlerInterface::class);

        $abstractClass->setErrorHandler($errorHandler);

        self::assertSame($errorHandler, self::getPrivatePropertyValue($abstractClass, 'errorHandler'));
    }
}
