<?php
declare(strict_types=1);

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

return static function (ContainerConfigurator $containerConfigurator): void {
    $containerConfigurator->extension('easy_webhook', [
        'signature' => [
            'header' => 'X-My-Header',
            'secret' => 'my-secret',
        ],
    ]);
};
