<?php
declare(strict_types=1);

namespace EonX\EasyApiToken\Tests\Bridge\Laravel;

use EonX\EasyApiToken\Bridge\Laravel\EasyApiTokenServiceProvider;
use EonX\EasyApiToken\Interfaces\Factories\EasyApiTokenDecoderFactoryInterface as DecoderFactoryInterface;
use EonX\EasyApiToken\Tests\AbstractLumenTestCase;

final class EasyApiTokenServiceProviderTest extends AbstractLumenTestCase
{
    /**
     * ServiceProvider should register the expected services.
     *
     * @return void
     */
    public function testRegister(): void
    {
        $app = $this->getApplication();
        $app->register(EasyApiTokenServiceProvider::class);

        self::assertInstanceOf(DecoderFactoryInterface::class, $app->get(DecoderFactoryInterface::class));
    }
}
