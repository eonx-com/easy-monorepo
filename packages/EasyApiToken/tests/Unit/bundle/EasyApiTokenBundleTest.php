<?php
declare(strict_types=1);

namespace EonX\EasyApiToken\Tests\Unit\Bundle;

use EonX\EasyApiToken\Common\Decoder\BasicAuthDecoder;
use EonX\EasyApiToken\Common\Decoder\DecoderInterface;
use EonX\EasyApiToken\Common\Exception\InvalidConfigurationException;
use EonX\EasyApiToken\Common\Factory\ApiTokenDecoderFactoryInterface;
use EonX\EasyApiToken\Tests\Unit\AbstractSymfonyTestCase;

final class EasyApiTokenBundleTest extends AbstractSymfonyTestCase
{
    public function testDecoderFactoryIsRegisteredAsService(): void
    {
        $kernel = $this->getKernel();
        $container = $kernel->getContainer();
        $decoderFactory = $container->get(ApiTokenDecoderFactoryInterface::class);

        self::assertInstanceOf(BasicAuthDecoder::class, $decoderFactory->build(BasicAuthDecoder::class));
        self::assertInstanceOf(BasicAuthDecoder::class, $container->get(DecoderInterface::class));

        $this->expectException(InvalidConfigurationException::class);
        $this->expectExceptionMessage('No decoder configured for key: "invalid".');

        $decoderFactory->build('invalid');
    }
}
