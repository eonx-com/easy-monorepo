<?php

declare(strict_types=1);

namespace EonX\EasyLogging\Tests\Bridge\Laravel;

use EonX\EasyLogging\Bridge\BridgeConstantsInterface;
use EonX\EasyLogging\Interfaces\LoggerFactoryInterface;
use Psr\Log\LoggerInterface;

final class EasyLoggingServiceProviderTest extends AbstractLaravelTestCase
{
    /**
     * @return iterable<mixed>
     */
    public function providerTestChannelParameterOnMake(): iterable
    {
        yield 'Default' => [null];
        yield 'App' => ['app'];
        yield 'Custom' => ['custom'];
    }

    /**
     * @dataProvider providerTestChannelParameterOnMake
     */
    public function testChannelParameterOnMake(?string $channel): void
    {
        $logger = $this->getApp()->make(LoggerInterface::class, [BridgeConstantsInterface::KEY_CHANNEL => $channel]);

        self::assertEquals($channel ?? LoggerFactoryInterface::DEFAULT_CHANNEL, $logger->getName());
    }

    public function testSanity(): void
    {
        $app = $this->getApp();

        self::assertInstanceOf(LoggerFactoryInterface::class, $app->get(LoggerFactoryInterface::class));
        self::assertInstanceOf(LoggerInterface::class, $app->get(LoggerInterface::class));
        self::assertInstanceOf(LoggerInterface::class, $app->get('logger'));
    }
}
