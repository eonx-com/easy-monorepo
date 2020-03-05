<?php
declare(strict_types=1);

namespace EonX\EasyApiToken\Tests\Bridge\Symfony;

use EonX\EasyApiToken\Decoders\BasicAuthDecoder;
use EonX\EasyApiToken\Decoders\ChainReturnFirstTokenDecoder;
use EonX\EasyApiToken\Exceptions\InvalidConfigurationException;
use EonX\EasyApiToken\Tests\AbstractTestCase;
use EonX\EasyApiToken\Tests\Bridge\Symfony\Stubs\KernelStub;
use EonX\EasyApiToken\Tests\Bridge\Symfony\Stubs\ServiceStub;

final class EasyApiTokenBundleTest extends AbstractTestCase
{
    public function testDecoderFactoryIsRegisteredAsService(): void
    {
        $kernel = new KernelStub();
        $kernel->boot();

        /** @var \EonX\EasyApiToken\Tests\Bridge\Symfony\Stubs\ServiceStub $stub */
        $stub = $kernel->getContainer()->get(ServiceStub::class);
        $decoderFactory = $stub->getDecoderFactory();

        self::assertInstanceOf(ChainReturnFirstTokenDecoder::class, $decoderFactory->build('chain'));
        self::assertInstanceOf(BasicAuthDecoder::class, $decoderFactory->build('basic'));

        self::expectException(InvalidConfigurationException::class);
        self::expectExceptionMessage('No decoder configured for key: "invalid".');

        $decoderFactory->build('invalid');
    }
}
