<?php
declare(strict_types=1);

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

use EonX\EasyWebhook\Bundle\Enum\ConfigServiceId;
use Symfony\Component\HttpClient\MockHttpClient;

return static function (ContainerConfigurator $containerConfigurator): void {
    $containerConfigurator->services()
        ->set(ConfigServiceId::HttpClient->value, MockHttpClient::class)
        ->public();
};
