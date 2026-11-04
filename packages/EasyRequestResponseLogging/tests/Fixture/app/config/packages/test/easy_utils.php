<?php
declare(strict_types=1);

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

return static function (ContainerConfigurator $containerConfigurator): void {
    $containerConfigurator->extension('easy_utils', [
        'sensitive_data_sanitizer' => [
            'keys_to_mask' => ['payment.secret'],
        ],
    ]);
};
