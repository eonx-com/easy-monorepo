<?php
declare(strict_types=1);

namespace EonX\EasyApiToken\Tests\Bridge\Symfony;

use EonX\EasyApiToken\Decoders\BasicAuthDecoder;
use EonX\EasyApiToken\Exceptions\InvalidConfigurationException;
use EonX\EasyApiToken\Interfaces\ApiTokenDecoderInterface;
use EonX\EasyApiToken\Interfaces\Factories\ApiTokenDecoderFactoryInterface;

final class EasyApiTokenBundleTest extends AbstractSymfonyTestCase
{
    /**
     * @throws \EonX\EasyApiToken\Exceptions\InvalidConfigurationException
     * @throws \EonX\EasyApiToken\Exceptions\InvalidDefaultDecoderException
     */
    public function testDecoderFactoryIsRegisteredAsService(): void
    {
        $kernel = $this->getKernel();
        $container = $kernel->getContainer();
        $decoderFactory = $container->get(ApiTokenDecoderFactoryInterface::class);

        self::assertInstanceOf(BasicAuthDecoder::class, $decoderFactory->build(BasicAuthDecoder::class));
        self::assertInstanceOf(BasicAuthDecoder::class, $container->get(ApiTokenDecoderInterface::class));

        self::expectException(InvalidConfigurationException::class);
        self::expectExceptionMessage('No decoder configured for key: "invalid".');

        $decoderFactory->build('invalid');
    }
}
