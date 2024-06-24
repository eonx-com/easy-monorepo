<?php
declare(strict_types=1);

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

use EonX\EasyHttpClient\Bundle\Enum\ConfigParam;
use EonX\EasyHttpClient\Bundle\Enum\ConfigServiceId;
use EonX\EasyHttpClient\Bundle\Enum\ConfigTag;
use EonX\EasyHttpClient\Common\HttpClient\WithEventsHttpClient;

return static function (ContainerConfigurator $container): void {
    $services = $container->services();
    $services->defaults()
        ->autoconfigure()
        ->autowire();

    $services
        ->set(ConfigServiceId::HttpClient->value, WithEventsHttpClient::class)
        ->arg('$modifiers', tagged_iterator(ConfigTag::RequestDataModifier->value))
        ->arg('$modifiersEnabled', '%' . ConfigParam::ModifiersEnabled->value . '%')
        ->arg('$modifiersWhitelist', '%' . ConfigParam::ModifiersWhitelist->value . '%');
};
