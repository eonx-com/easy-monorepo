<?php
declare(strict_types=1);

namespace EonX\EasyApiToken\Tests\Unit\Laravel;

use EonX\EasyApiToken\Bundle\Enum\ConfigTag;
use EonX\EasyApiToken\Common\Decoder\BasicAuthDecoder;
use EonX\EasyApiToken\Common\Decoder\DecoderInterface;
use EonX\EasyApiToken\Common\Factory\ApiTokenDecoderFactoryInterface;
use EonX\EasyApiToken\Laravel\EasyApiTokenServiceProvider;
use EonX\EasyApiToken\Tests\Stub\Common\Provider\DecoderProviderStub;

final class EasyApiTokenServiceProviderTest extends AbstractLumenTestCase
{
    public function testRegister(): void
    {
        $app = $this->getApplication();

        $app->singleton(DecoderProviderStub::class);
        $app->tag(DecoderProviderStub::class, [ConfigTag::DecoderProvider->value]);
        $app->register(EasyApiTokenServiceProvider::class);

        self::assertInstanceOf(
            ApiTokenDecoderFactoryInterface::class,
            $app->get(ApiTokenDecoderFactoryInterface::class)
        );
        self::assertInstanceOf(BasicAuthDecoder::class, $app->get(DecoderInterface::class));
    }
}
