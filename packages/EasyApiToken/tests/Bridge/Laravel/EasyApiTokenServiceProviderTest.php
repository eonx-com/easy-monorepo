<?php
declare(strict_types=1);

namespace LoyaltyCorp\EasyApiToken\Tests\Bridge\Laravel;

use LoyaltyCorp\EasyApiToken\Bridge\Laravel\EasyApiTokenServiceProvider;
use LoyaltyCorp\EasyApiToken\Interfaces\Factories\EasyApiTokenDecoderFactoryInterface as DecoderFactoryInterface;
use LoyaltyCorp\EasyApiToken\Tests\AbstractLumenTestCase;

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
