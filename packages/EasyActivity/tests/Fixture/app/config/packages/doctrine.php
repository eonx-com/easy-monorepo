<?php
declare(strict_types=1);

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

use EonX\EasyDoctrine\Common\Type\CarbonImmutableDateTimeMicrosecondsType;

return App::config([
    'doctrine' => [
        'dbal' => [
            'types' => [
                'datetime_immutable' => [
                    'class' => CarbonImmutableDateTimeMicrosecondsType::class,
                ],
            ],
            'connections' => [
                'default' => [
                    'driver' => 'pdo_sqlite',
                    'url' => 'sqlite:///:memory:',
                ],
            ],
        ],
        'orm' => [
            'auto_generate_proxy_classes' => true,
            'entity_managers' => [
                'default' => [
                    'naming_strategy' => 'doctrine.orm.naming_strategy.underscore_number_aware',
                    'mappings' => [
                        'AppEntity' => [
                            'dir' => param('kernel.project_dir') . '/src/Entity',
                            'is_bundle' => false,
                            'prefix' => 'EonX\EasyActivity\Tests\Fixture\App\Entity',
                            'type' => 'attribute',
                        ],
                    ],
                ],
            ],
        ],
    ],
]);
