<?php
declare(strict_types=1);

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

use EonX\EasyUtils\Bundle\Enum\ConfigParam;
use EonX\EasyUtils\Bundle\Enum\ConfigTag;
use EonX\EasyUtils\SensitiveData\Sanitizer\SensitiveDataSanitizer;
use EonX\EasyUtils\SensitiveData\Sanitizer\SensitiveDataSanitizerInterface;

return static function (ContainerConfigurator $containerConfigurator): void {
    $services = $containerConfigurator->services();
    $services->defaults()
        ->autowire()
        ->autoconfigure();

    $services
        ->set(SensitiveDataSanitizerInterface::class, SensitiveDataSanitizer::class)
        ->arg('$keysToMask', param(ConfigParam::SensitiveDataKeysToMask->value))
        ->arg('$maskPattern', param(ConfigParam::SensitiveDataMaskPattern->value))
        ->arg('$objectTransformers', tagged_iterator(ConfigTag::SensitiveDataObjectTransformer->value))
        ->arg('$stringSanitizers', tagged_iterator(ConfigTag::SensitiveDataStringSanitizer->value));
};
