<?php
declare(strict_types=1);

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

use EonX\EasyHttpClient\Common\Listener\AwsMessagingRequestSanitizerListener;

return static function (ContainerConfigurator $container): void {
    $services = $container->services();

    $services
        ->set(AwsMessagingRequestSanitizerListener::class)
        ->tag('kernel.event_listener', [
            'priority' => 100,
        ]);
};
