<?php

declare(strict_types=1);

namespace EonX\EasyApiToken\Tests\Bridge\Symfony;

use EonX\EasyApiToken\Decoders\BasicAuthDecoder;
use EonX\EasyApiToken\Exceptions\InvalidConfigurationException;
use EonX\EasyApiToken\Interfaces\ApiTokenDecoderInterface;
use EonX\EasyApiToken\Interfaces\Factories\ApiTokenDecoderFactoryInterface;
use EonX\EasyApiToken\Tests\AbstractTestCase;
use EonX\EasyApiToken\Tests\Bridge\Symfony\Stubs\KernelStub;

final class EasyApiTokenBundleTest extends AbstractTestCase
{
    public function testDecoderFactoryIsRegisteredAsService(): void
    {
        $kernel = new KernelStub();
        $kernel->boot();

        $container = $kernel->getContainer();
        $decoderFactory = $container->get(ApiTokenDecoderFactoryInterface::class);

        self::assertInstanceOf(BasicAuthDecoder::class, $decoderFactory->build(BasicAuthDecoder::class));
        self::assertInstanceOf(BasicAuthDecoder::class, $container->get(ApiTokenDecoderInterface::class));

        self::expectException(InvalidConfigurationException::class);
        self::expectExceptionMessage('No decoder configured for key: "invalid".');

        $decoderFactory->build('invalid');
    }
}
