<?php
declare(strict_types=1);

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

return App::config([
    'doctrine' => [
        'dbal' => [
            'connections' => [
                'default' => [
                    'driver' => 'pdo_sqlite',
                    'url' => 'sqlite:///:memory:',
                ],
            ],
        ],
        'orm' => [
            'enable_native_lazy_objects' => true,
            'entity_managers' => [
                'default' => [
                    'naming_strategy' => 'doctrine.orm.naming_strategy.underscore_number_aware',
                    'mappings' => [
                        'AppEntity' => [
                            'dir' => param('kernel.project_dir') . '/src/Entity',
                            'is_bundle' => false,
                            'prefix' => 'EonX\EasyBugsnag\Tests\Fixture\App\Entity',
                            'type' => 'attribute',
                        ],
                    ],
                ],
            ],
        ],
    ],
]);
