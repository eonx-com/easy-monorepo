<?php

declare(strict_types=1);

namespace EonX\EasyLogging\Tests\Bridge\Symfony\Traits;

use EonX\EasyLogging\Bridge\Symfony\Traits\LoggerAwareTrait;
use EonX\EasyLogging\Tests\AbstractTestCase;
use Psr\Log\LoggerInterface;

/**
 * @covers \EonX\EasyLogging\Bridge\Symfony\Traits\LoggerAwareTrait
 */
final class LoggerAwareTraitTest extends AbstractTestCase
{
    public function testSetLoggerSucceeds(): void
    {
        $abstractClass = new class() {
            use LoggerAwareTrait;
        };
        /** @var \Psr\Log\LoggerInterface $logger */
        $logger = $this->prophesize(LoggerInterface::class)->reveal();

        $abstractClass->setLogger($logger);

        self::assertSame($logger, $this->getPrivatePropertyValue($abstractClass, 'logger'));
    }
}
