<?php
declare(strict_types=1);

namespace EonX\EasyApiToken\Tests\Bridge\Laravel;

use EonX\EasyApiToken\Bridge\BridgeConstantsInterface;
use EonX\EasyApiToken\Bridge\Laravel\EasyApiTokenServiceProvider;
use EonX\EasyApiToken\Decoders\BasicAuthDecoder;
use EonX\EasyApiToken\Interfaces\ApiTokenDecoderInterface;
use EonX\EasyApiToken\Interfaces\Factories\ApiTokenDecoderFactoryInterface as DecoderFactoryInterface;
use EonX\EasyApiToken\Tests\AbstractLumenTestCase;
use EonX\EasyApiToken\Tests\Stubs\DecoderProviderStub;

final class EasyApiTokenServiceProviderTest extends AbstractLumenTestCase
{
    public function testRegister(): void
    {
        $app = $this->getApplication();

        $app->singleton(DecoderProviderStub::class);
        $app->tag(DecoderProviderStub::class, [BridgeConstantsInterface::TAG_DECODER_PROVIDER]);
        $app->register(EasyApiTokenServiceProvider::class);

        self::assertInstanceOf(DecoderFactoryInterface::class, $app->get(DecoderFactoryInterface::class));
        self::assertInstanceOf(BasicAuthDecoder::class, $app->get(ApiTokenDecoderInterface::class));
    }
}
