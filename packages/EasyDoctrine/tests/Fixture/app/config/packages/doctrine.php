<?php
declare(strict_types=1);

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

use Doctrine\DBAL\Types\Types;
use EonX\EasyDoctrine\Common\Function\Cast;
use EonX\EasyDoctrine\Common\Function\Contains;
use EonX\EasyDoctrine\Common\Function\StringAgg;
use EonX\EasyDoctrine\Common\Type\CarbonImmutableDateTimeMicrosecondsType;
use EonX\EasyDoctrine\Tests\Fixture\App\Type\PriceType;

return App::config([
    'doctrine' => [
        'dbal' => [
            'types' => [
                PriceType::NAME => [
                    'class' => PriceType::class,
                ],
                Types::DATETIME_IMMUTABLE => [
                    'class' => CarbonImmutableDateTimeMicrosecondsType::class,
                ],
            ],
            'connections' => [
                'default' => [
                    'use_savepoints' => true,
                    'driver' => 'pdo_sqlite',
                    'url' => 'sqlite:///:memory:',
                ],
            ],
        ],
        'orm' => [
            'auto_generate_proxy_classes' => true,
            'entity_managers' => [
                'default' => [
                    'dql' => [
                        'string_functions' => [
                            'CAST' => Cast::class,
                            'CONTAINS' => Contains::class,
                            'STRING_AGG' => StringAgg::class,
                        ],
                    ],
                    'naming_strategy' => 'doctrine.orm.naming_strategy.underscore_number_aware',
                    'mappings' => [
                        'AppEntity' => [
                            'dir' => param('kernel.project_dir') . '/src/Entity',
                            'is_bundle' => false,
                            'prefix' => 'EonX\EasyDoctrine\Tests\Fixture\App\Entity',
                            'type' => 'attribute',
                        ],
                    ],
                ],
            ],
        ],
    ],
]);
