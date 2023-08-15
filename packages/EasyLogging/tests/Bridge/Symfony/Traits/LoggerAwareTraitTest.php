<?php
declare(strict_types=1);

namespace EonX\EasyLogging\Tests\Bridge\Symfony\Traits;

use EonX\EasyLogging\Bridge\Symfony\Traits\LoggerAwareTrait;
use EonX\EasyLogging\Tests\AbstractTestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use Psr\Log\LoggerInterface;

#[CoversClass(LoggerAwareTrait::class)]
final class LoggerAwareTraitTest extends AbstractTestCase
{
    public function testSetLoggerSucceeds(): void
    {
        $abstractClass = new class() {
            use LoggerAwareTrait;
        };
        $logger = $this->createMock(LoggerInterface::class);

        $abstractClass->setLogger($logger);

        self::assertSame($logger, $this->getPrivatePropertyValue($abstractClass, 'logger'));
    }
}
