<?php

declare(strict_types=1);

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

use EonX\EasyUtils\SensitiveData\StringSanitizers\AuthorizationStringSanitizer;
use EonX\EasyUtils\SensitiveData\StringSanitizers\CreditCardNumberStringSanitizer;
use EonX\EasyUtils\SensitiveData\StringSanitizers\JsonStringSanitizer;
use EonX\EasyUtils\SensitiveData\StringSanitizers\UrlStringSanitizer;

return static function (ContainerConfigurator $containerConfigurator): void {
    $services = $containerConfigurator->services();
    $services->defaults()
        ->autowire()
        ->autoconfigure();

    $sanitizers = [
        AuthorizationStringSanitizer::class,
        CreditCardNumberStringSanitizer::class,
        JsonStringSanitizer::class,
        UrlStringSanitizer::class,
    ];

    foreach ($sanitizers as $sanitizer) {
        $services
            ->set($sanitizer)
            ->arg('$priority', 10000);
    }
};
