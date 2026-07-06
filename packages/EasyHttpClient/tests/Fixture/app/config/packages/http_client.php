<?php
declare(strict_types=1);

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

use EonX\EasyTest\HttpClient\Factory\TestResponseFactory;
use Symfony\Component\HttpClient\MockHttpClient;

/*
 * Register the mock transport as "http_client.transport" (the service EasyHttpClient decorates) instead of using
 * framework.http_client.mock_response_factory. Since Symfony 8, mock_response_factory wires the mock into a separate
 * ".http_client.mock_transport.*" service and repoints "http_client" to it, bypassing "http_client.transport" and
 * therefore the WithEventsHttpClient decoration. Overriding the transport works on both Symfony 7.4 and 8.
 */
return static function (ContainerConfigurator $containerConfigurator): void {
    $containerConfigurator->services()
        ->set('http_client.transport', MockHttpClient::class)
        ->args([service(TestResponseFactory::class)]);
};
