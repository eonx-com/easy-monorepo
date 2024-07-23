<?php
declare(strict_types=1);

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

use EonX\EasyUtils\Bundle\Enum\ConfigParam;
use EonX\EasyUtils\Common\Normalizer\TrimStringsNormalizer;
use EonX\EasyUtils\Common\Trimmer\RecursiveStringTrimmer;
use EonX\EasyUtils\Common\Trimmer\StringTrimmerInterface;

return static function (ContainerConfigurator $container): void {
    $services = $container->services();

    $services->defaults()
        ->autowire()
        ->autoconfigure();

    $services->set(StringTrimmerInterface::class, RecursiveStringTrimmer::class);

    $services->set(TrimStringsNormalizer::class)
        ->arg('$exceptKeys', param(ConfigParam::StringTrimmerExceptKeys->value))
        ->tag('serializer.normalizer');
};
