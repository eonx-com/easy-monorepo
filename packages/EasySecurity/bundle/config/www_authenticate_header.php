<?php
declare(strict_types=1);

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

use EonX\EasyApiToken\Common\Factory\ApiTokenDecoderFactoryInterface;
use EonX\EasySecurity\Bundle\Enum\ConfigParam;
use EonX\EasySecurity\SymfonySecurity\Listener\AddWwwAuthenticateHeaderListener;
use EonX\EasySecurity\SymfonySecurity\Resolver\WwwAuthenticateHeaderResolver;

return static function (ContainerConfigurator $containerConfigurator): void {
    $services = $containerConfigurator->services();
    $services->defaults()
        ->autowire()
        ->autoconfigure();

    $services
        ->set(WwwAuthenticateHeaderResolver::class)
        ->arg('$defaultChallenge', param(ConfigParam::WwwAuthenticateHeaderDefaultChallenge->value))
        ->arg('$apiTokenDecoderFactory', service(ApiTokenDecoderFactoryInterface::class)->nullOnInvalid())
        ->arg('$apiTokenDecoder', param(ConfigParam::TokenDecoder->value));

    $services
        ->set(AddWwwAuthenticateHeaderListener::class)
        ->tag('kernel.event_listener');
};
