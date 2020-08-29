<?php

declare(strict_types=1);

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

use EonX\EasyCore\Bridge\BridgeConstantsInterface;
use EonX\EasyCore\Helpers\RecursiveStringsTrimmer;
use EonX\EasyCore\Helpers\StringsTrimmerInterface;

return static function (ContainerConfigurator $container): void {
    $services = $container->services();

    $services->defaults()
        ->autowire()
        ->autoconfigure();

    $services->set(StringsTrimmerInterface::class, RecursiveStringsTrimmer::class);

    $services->set(\EonX\EasyCore\Bridge\Symfony\Serializer\TrimStringsDenormalizer::class)
        ->arg('$decorated', ref('.inner'))
        ->arg('$except', '%' . BridgeConstantsInterface::PARAM_TRIM_STRINGS_EXCEPT . '%')
        ->decorate('api_platform.jsonapi.normalizer.item')
        ->tag('serializer.normalizer')
    ;
};
