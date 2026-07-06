<?php
declare(strict_types=1);

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

use EonX\EasyTest\HttpClient\Factory\TestResponseFactory;
use Symfony\Component\HttpClient\MockHttpClient;

return static function (ContainerConfigurator $containerConfigurator): void {
    $containerConfigurator->services()
        ->set('http_client.transport', MockHttpClient::class)
        ->args([service(TestResponseFactory::class)]);
};
