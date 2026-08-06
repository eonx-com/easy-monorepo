<?php
declare(strict_types=1);

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

use EonX\EasyActivity\Tests\Fixture\App\Entity\User;

return static function (ContainerConfigurator $containerConfigurator): void {
    // Configuration for testing encryptable fields masking integration
    $containerConfigurator->extension('easy_activity', [
        'disallowed_properties' => [
            'email',
        ],
        'subjects' => [
            User::class => [
                'type' => 'User',
                'disallowed_properties' => [
                    'password',
                ],
            ],
        ],
    ]);
};
