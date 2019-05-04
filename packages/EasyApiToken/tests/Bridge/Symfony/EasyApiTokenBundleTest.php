<?php
declare(strict_types=1);

namespace LoyaltyCorp\EasyApiToken\Tests\Bridge\Symfony;

use LoyaltyCorp\EasyApiToken\Decoders\BasicAuthDecoder;
use LoyaltyCorp\EasyApiToken\Decoders\ChainReturnFirstTokenDecoder;
use LoyaltyCorp\EasyApiToken\Exceptions\InvalidConfigurationException;
use LoyaltyCorp\EasyApiToken\Tests\AbstractTestCase;
use LoyaltyCorp\EasyApiToken\Tests\Bridge\Symfony\Stubs\KernelStub;
use LoyaltyCorp\EasyApiToken\Tests\Bridge\Symfony\Stubs\ServiceStub;

final class EasyApiTokenBundleTest extends AbstractTestCase
{
    /**
     * Bundle should register decoder factory as service with expected config.
     *
     * @return void
     *
     * @throws \LoyaltyCorp\EasyApiToken\Exceptions\InvalidConfigurationException
     */
    public function testDecoderFactoryIsRegisteredAsService(): void
    {
        $kernel = new KernelStub();
        $kernel->boot();

        $container = $kernel->getContainer();
        $decoderFactory = $container->get(ServiceStub::class)->getDecoderFactory();

        self::assertInstanceOf(ChainReturnFirstTokenDecoder::class, $decoderFactory->build('chain'));
        self::assertInstanceOf(BasicAuthDecoder::class, $decoderFactory->build('basic'));

        self::expectException(InvalidConfigurationException::class);
        self::expectExceptionMessage('No decoder configured for key: "invalid".');

        $decoderFactory->build('invalid');
    }
}
