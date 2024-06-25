<?php
declare(strict_types=1);

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

use EonX\EasyEventDispatcher\Dispatcher\EventDispatcherInterface;
use EonX\EasyHttpClient\Tests\Fixture\App\Client\SomeClient;
use EonX\EasyHttpClient\Tests\Stub\Dispatcher\EventDispatcherStub;
use EonX\EasyTest\HttpClient\TestResponseFactory;

return static function (ContainerConfigurator $containerConfigurator): void {
    $services = $containerConfigurator->services();

    $services->defaults()
        ->autowire()
        ->autoconfigure();

    $services->set(TestResponseFactory::class);
    $services->set(EventDispatcherInterface::class, EventDispatcherStub::class);

    $services->set(SomeClient::class)
        ->public();
};
