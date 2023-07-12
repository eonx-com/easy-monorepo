<?php

declare(strict_types=1);

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

use EonX\EasyUtils\Bridge\BridgeConstantsInterface;
use EonX\EasyUtils\Bridge\Symfony\Normalizers\TrimStringsNormalizer;
use EonX\EasyUtils\StringTrimmers\RecursiveStringTrimmer;
use EonX\EasyUtils\StringTrimmers\StringTrimmerInterface;

return static function (ContainerConfigurator $container): void {
    $services = $container->services();

    $services->defaults()
        ->autowire()
        ->autoconfigure();

    $services->set(StringTrimmerInterface::class, RecursiveStringTrimmer::class);

    $services->set(TrimStringsNormalizer::class)
        ->arg('$exceptKeys', '%' . BridgeConstantsInterface::PARAM_STRING_TRIMMER_EXCEPT_KEYS . '%')
        ->tag('serializer.normalizer');
};
