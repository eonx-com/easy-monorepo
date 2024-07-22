<?php
declare(strict_types=1);

namespace EonX\EasyLogging\Tests\Unit\Logger;

use EonX\EasyLogging\Logger\LoggerAwareTrait;
use EonX\EasyLogging\Tests\Unit\AbstractUnitTestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use Psr\Log\LoggerInterface;

#[CoversClass(LoggerAwareTrait::class)]
final class LoggerAwareTraitTest extends AbstractUnitTestCase
{
    public function testSetLoggerSucceeds(): void
    {
        $abstractClass = new class() {
            use LoggerAwareTrait;
        };
        $logger = $this->createMock(LoggerInterface::class);

        $abstractClass->setLogger($logger);

        self::assertSame($logger, self::getPrivatePropertyValue($abstractClass, 'logger'));
    }
}
