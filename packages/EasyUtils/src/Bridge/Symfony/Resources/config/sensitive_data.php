<?php
declare(strict_types=1);

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

use EonX\EasyUtils\Bridge\BridgeConstantsInterface;
use EonX\EasyUtils\SensitiveData\SensitiveDataSanitizer;
use EonX\EasyUtils\SensitiveData\SensitiveDataSanitizerInterface;

return static function (ContainerConfigurator $containerConfigurator): void {
    $services = $containerConfigurator->services();
    $services->defaults()
        ->autowire()
        ->autoconfigure();

    $services
        ->set(SensitiveDataSanitizerInterface::class, SensitiveDataSanitizer::class)
        ->arg('$useDefaultKeysToMask', param(BridgeConstantsInterface::PARAM_SENSITIVE_DATA_USE_DEFAULT_KEYS_TO_MASK))
        ->arg('$keysToMask', param(BridgeConstantsInterface::PARAM_SENSITIVE_DATA_KEYS_TO_MASK))
        ->arg('$maskPattern', param(BridgeConstantsInterface::PARAM_SENSITIVE_DATA_MASK_PATTERN))
        ->arg('$objectTransformers', tagged_iterator(BridgeConstantsInterface::TAG_SENSITIVE_DATA_OBJECT_TRANSFORMER))
        ->arg('$stringSanitizers', tagged_iterator(BridgeConstantsInterface::TAG_SENSITIVE_DATA_STRING_SANITIZER));
};
