<?php

declare(strict_types=1);

namespace EonX\EasySwoole\Tests\Bridge\Symfony;

use EonX\EasySwoole\Bridge\Symfony\DependencyInjection\Configuration;
use EonX\EasyUtils\Helpers\ArrayHelper;
use Symfony\Component\Config\Definition\Processor;

final class ConfigurationTest extends AbstractSymfonyTestCase
{
    private const DEFAULT_CONFIG = [
        'access_log' => [
            'enabled' => true,
            'timezone' => 'UTC',
        ],
        'doctrine' => [
            'enabled' => true,
            'reset_dbal_connections' => true,
        ],
        'easy_batch' => [
            'enabled' => true,
            'reset_batch_processor' => true,
        ],
        'request_limits' => [
            'enabled' => true,
            'min' => 5000,
            'max' => 10000,
        ],
        'reset_services' => [
            'enabled' => true,
        ],
        'static_php_files' => [
            'allowed_dirs' => ['%kernel.project_dir%/public'],
            'allowed_filenames' => [],
            'enabled' => false,
        ],
    ];

    /**
     * @return iterable<mixed>
     */
    public function providerTestConfiguration(): iterable
    {
        yield 'No configuration set' => [
            [],
            self::DEFAULT_CONFIG,
        ];

        yield 'Disable access_log' => [
            [
                [
                    'access_log' => [
                        'enabled' => false,
                    ],
                ],
            ],
            ArrayHelper::smartReplace(self::DEFAULT_CONFIG, ['access_log' => ['enabled' => false]]),
        ];

        yield 'Normalize allowed static php files' => [
            [
                [
                    'static_php_files' => [
                        'allowed_filenames' => 'hash.php',
                    ],
                ],
            ],
            ArrayHelper::smartReplace(self::DEFAULT_CONFIG, [
                'static_php_files' => [
                    'allowed_filenames' => ['/hash.php'],
                ],
            ]),
        ];

        yield 'Normalize allowed static php files - 1' => [
            [
                [
                    'static_php_files' => [
                        'allowed_filenames' => '/hash.php',
                    ],
                ],
            ],
            ArrayHelper::smartReplace(self::DEFAULT_CONFIG, [
                'static_php_files' => [
                    'allowed_filenames' => ['/hash.php'],
                ],
            ]),
        ];

        yield 'Normalize allowed static php files - support null' => [
            [
                [
                    'static_php_files' => [
                        'allowed_filenames' => null,
                    ],
                ],
            ],
            self::DEFAULT_CONFIG,
        ];

        yield 'Normalize allowed static php dirs - support null' => [
            [
                [
                    'static_php_files' => [
                        'allowed_dirs' => null,
                    ],
                ],
            ],
            self::DEFAULT_CONFIG,
        ];

        yield 'Normalize allowed static php dirs - filter empty strings' => [
            [
                [
                    'static_php_files' => [
                        'allowed_dirs' => '/',
                    ],
                ],
            ],
            ArrayHelper::smartReplace(self::DEFAULT_CONFIG, [
                'static_php_files' => [
                    'allowed_dirs' => ['%kernel.project_dir%/public'],
                ],
            ]),
        ];

        yield 'Normalize allowed static php dirs - trim trailing slash' => [
            [
                [
                    'static_php_files' => [
                        'allowed_dirs' => '/path/to/dir/',
                    ],
                ],
            ],
            ArrayHelper::smartReplace(self::DEFAULT_CONFIG, [
                'static_php_files' => [
                    'allowed_dirs' => ['/path/to/dir'],
                ],
            ]),
        ];
    }

    /**
     * @param mixed[] $configs
     * @param mixed[] $expectedConfig
     *
     * @dataProvider providerTestConfiguration
     */
    public function testConfiguration(array $configs, array $expectedConfig): void
    {
        $config = (new Processor())->processConfiguration(new Configuration(), $configs);

        self::assertEquals($expectedConfig, $config);
    }
}
