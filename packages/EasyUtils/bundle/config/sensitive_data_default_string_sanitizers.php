<?php
declare(strict_types=1);

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

use EonX\EasyUtils\SensitiveData\Sanitizer\AuthorizationStringSanitizer;
use EonX\EasyUtils\SensitiveData\Sanitizer\CreditCardNumberStringSanitizer;
use EonX\EasyUtils\SensitiveData\Sanitizer\UrlStringSanitizer;

return static function (ContainerConfigurator $containerConfigurator): void {
    $services = $containerConfigurator->services();
    $services->defaults()
        ->autowire()
        ->autoconfigure();

    $sanitizers = [
        AuthorizationStringSanitizer::class,
        CreditCardNumberStringSanitizer::class,
        UrlStringSanitizer::class,
    ];

    foreach ($sanitizers as $sanitizer) {
        $services
            ->set($sanitizer)
            ->arg('$priority', 10000);
    }
};
