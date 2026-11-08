<?php
declare(strict_types=1);

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

return static function (ContainerConfigurator $containerConfigurator): void {
    $containerConfigurator->extension('easy_batch', [
        'allow_unsigned_messages' => true,
        'signing_secret' => ['new-secret', 'old-secret'],
    ]);
};
