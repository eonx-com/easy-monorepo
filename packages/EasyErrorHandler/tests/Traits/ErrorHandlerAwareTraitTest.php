<?php
declare(strict_types=1);

namespace EonX\EasyErrorHandler\Tests\Traits;

use EonX\EasyErrorHandler\Interfaces\ErrorHandlerInterface;
use EonX\EasyErrorHandler\Tests\AbstractTestCase;
use EonX\EasyErrorHandler\Traits\ErrorHandlerAwareTrait;

final class ErrorHandlerAwareTraitTest extends AbstractTestCase
{
    public function testSetErrorHandlerSucceeds(): void
    {
        $abstractClass = new class() {
            use ErrorHandlerAwareTrait;
        };
        $errorHandler = $this->createMock(ErrorHandlerInterface::class);

        $abstractClass->setErrorHandler($errorHandler);

        self::assertSame($errorHandler, $this->getPrivatePropertyValue($abstractClass, 'errorHandler'));
    }
}
