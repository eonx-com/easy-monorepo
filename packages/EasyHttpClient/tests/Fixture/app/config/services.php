<?php
declare(strict_types=1);

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

use EonX\EasyHttpClient\Tests\Fixture\App\Client\SomeClient;
use EonX\EasyTest\EasyEventDispatcher\Dispatcher\EventDispatcherStub;
use EonX\EasyTest\HttpClient\Factory\TestResponseFactory;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;

return static function (ContainerConfigurator $containerConfigurator): void {
    $services = $containerConfigurator->services();

    $services->defaults()
        ->autowire()
        ->autoconfigure();

    $services->set(TestResponseFactory::class);

    $services->set(EventDispatcherStub::class)
        ->decorate(EventDispatcherInterface::class)
        ->public();

    $services->set(SomeClient::class)
        ->public();
};
