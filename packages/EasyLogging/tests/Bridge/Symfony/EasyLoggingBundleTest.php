<?php
declare(strict_types=1);

namespace EonX\EasyLogging\Tests\Bridge\Symfony;

use EonX\EasyLogging\Interfaces\LoggerFactoryInterface;
use Psr\Log\LoggerInterface;

final class EasyLoggingBundleTest extends AbstractSymfonyTestCase
{
    public function testSanity(): void
    {
        $container = $this->getKernel()
            ->getContainer();

        self::assertInstanceOf(LoggerFactoryInterface::class, $container->get(LoggerFactoryInterface::class));
        self::assertInstanceOf(LoggerInterface::class, $container->get(LoggerInterface::class));
        self::assertInstanceOf(LoggerInterface::class, $container->get('logger'));
        self::assertInstanceOf(LoggerInterface::class, $container->get('easy_logging.logger'));
    }
}
