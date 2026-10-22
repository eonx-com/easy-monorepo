<?php
declare(strict_types=1);

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

use Bugsnag\Client;

return static function (ContainerConfigurator $containerConfigurator): void {
    // Base of the Bugsnag handler fixtures: the Bugsnag client the handler needs and the monolog channels to attach
    // it to; the "easy_logging.bugsnag_handler" option itself comes from the other fixtures
    $containerConfigurator->extension('monolog', [
        'channels' => ['other'],
        'handlers' => [
            'main' => [
                'type' => 'test',
            ],
        ],
    ]);

    $containerConfigurator->services()
        ->set(Client::class)
        ->factory([Client::class, 'make'])
        ->args(['some-api-key']);
};
