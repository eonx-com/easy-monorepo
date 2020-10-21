<?php

declare(strict_types=1);

namespace EonX\EasyApiToken\Tests\Bridge\Laravel;

use EonX\EasyApiToken\Bridge\Laravel\EasyApiTokenServiceProvider;
use EonX\EasyApiToken\Decoders\BasicAuthDecoder;
use EonX\EasyApiToken\Interfaces\ApiTokenDecoderInterface;
use EonX\EasyApiToken\Interfaces\Factories\ApiTokenDecoderFactoryInterface as DecoderFactoryInterface;
use EonX\EasyApiToken\Tests\AbstractLumenTestCase;

final class EasyApiTokenServiceProviderTest extends AbstractLumenTestCase
{
    public function testRegister(): void
    {
        $app = $this->getApplication();

        \config([
            'easy-api-token' => [
                'decoders' => [
                    'basic' => null,
                ],
                'default_decoder' => 'basic',
            ],
        ]);

        $app->register(EasyApiTokenServiceProvider::class);

        self::assertInstanceOf(DecoderFactoryInterface::class, $app->get(DecoderFactoryInterface::class));
        self::assertInstanceOf(BasicAuthDecoder::class, $app->get(ApiTokenDecoderInterface::class));
    }
}
