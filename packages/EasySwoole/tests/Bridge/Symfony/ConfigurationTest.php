<?php
declare(strict_types=1);

namespace EonX\EasySwoole\Tests\Bridge\Symfony;

use EonX\EasySwoole\Bridge\Symfony\EasySwooleSymfonyBundle;
use EonX\EasyUtils\Helpers\ArrayHelper;
use PHPUnit\Framework\Attributes\DataProvider;
use Symfony\Component\Config\Definition\Configuration;
use Symfony\Component\Config\Definition\Processor;

final class ConfigurationTest extends AbstractSymfonyTestCase
{
    private const DEFAULT_CONFIG = [
        'access_log' => [
            'enabled' => true,
            'do_not_log_paths' => [],
            'timezone' => 'UTC',
        ],
        'doctrine' => [
            'enabled' => true,
            'reset_dbal_connections' => true,
            'coroutine_pdo' => [
                'enabled' => false,
                'default_heartbeat' => true,
                'default_max_idle_time' => 60.0,
                'default_pool_size' => 10,
            ],
        ],
        'easy_admin' => [
            'enabled' => true,
        ],
        'easy_batch' => [
            'enabled' => true,
            'reset_batch_processor' => true,
        ],
        'easy_bugsnag' => [
            'enabled' => true,
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
     * @see testConfiguration
     */
    public static function providerTestConfiguration(): iterable
    {
        yield 'No configuration set' => [
            'configs' => [],
            'expectedConfig' => self::DEFAULT_CONFIG,
        ];

        yield 'Disable access_log' => [
            'configs' => [
                [
                    'access_log' => [
                        'enabled' => false,
                    ],
                ],
            ],
            'expectedConfig' => ArrayHelper::smartReplace(self::DEFAULT_CONFIG, ['access_log' => ['enabled' => false]]),
        ];

        yield 'Normalize do not log paths' => [
            'configs' => [
                [
                    'access_log' => [
                        'do_not_log_paths' => 'hash.php',
                    ],
                ],
            ],
            'expectedConfig' => ArrayHelper::smartReplace(self::DEFAULT_CONFIG, [
                'access_log' => [
                    'do_not_log_paths' => ['/hash.php'],
                ],
            ]),
        ];

        yield 'Normalize do not log paths - 1' => [
            'configs' => [
                [
                    'access_log' => [
                        'do_not_log_paths' => '/hash.php',
                    ],
                ],
            ],
            'expectedConfig' => ArrayHelper::smartReplace(self::DEFAULT_CONFIG, [
                'access_log' => [
                    'do_not_log_paths' => ['/hash.php'],
                ],
            ]),
        ];

        yield 'Normalize do not log paths - support null' => [
            'configs' => [
                [
                    'access_log' => [
                        'do_not_log_paths' => null,
                    ],
                ],
            ],
            'expectedConfig' => self::DEFAULT_CONFIG,
        ];

        yield 'Normalize allowed static php files' => [
            'configs' => [
                [
                    'static_php_files' => [
                        'allowed_filenames' => 'hash.php',
                    ],
                ],
            ],
            'expectedConfig' => ArrayHelper::smartReplace(self::DEFAULT_CONFIG, [
                'static_php_files' => [
                    'allowed_filenames' => ['/hash.php'],
                ],
            ]),
        ];

        yield 'Normalize allowed static php files - 1' => [
            'configs' => [
                [
                    'static_php_files' => [
                        'allowed_filenames' => '/hash.php',
                    ],
                ],
            ],
            'expectedConfig' => ArrayHelper::smartReplace(self::DEFAULT_CONFIG, [
                'static_php_files' => [
                    'allowed_filenames' => ['/hash.php'],
                ],
            ]),
        ];

        yield 'Normalize allowed static php files - support null' => [
            'configs' => [
                [
                    'static_php_files' => [
                        'allowed_filenames' => null,
                    ],
                ],
            ],
            'expectedConfig' => self::DEFAULT_CONFIG,
        ];

        yield 'Normalize allowed static php dirs - support null' => [
            'configs' => [
                [
                    'static_php_files' => [
                        'allowed_dirs' => null,
                    ],
                ],
            ],
            'expectedConfig' => self::DEFAULT_CONFIG,
        ];

        yield 'Normalize allowed static php dirs - filter empty strings' => [
            'configs' => [
                [
                    'static_php_files' => [
                        'allowed_dirs' => '/',
                    ],
                ],
            ],
            'expectedConfig' => ArrayHelper::smartReplace(self::DEFAULT_CONFIG, [
                'static_php_files' => [
                    'allowed_dirs' => ['%kernel.project_dir%/public'],
                ],
            ]),
        ];

        yield 'Normalize allowed static php dirs - trim trailing slash' => [
            'configs' => [
                [
                    'static_php_files' => [
                        'allowed_dirs' => '/path/to/dir/',
                    ],
                ],
            ],
            'expectedConfig' => ArrayHelper::smartReplace(self::DEFAULT_CONFIG, [
                'static_php_files' => [
                    'allowed_dirs' => ['/path/to/dir'],
                ],
            ]),
        ];
    }

    #[DataProvider('providerTestConfiguration')]
    public function testConfiguration(array $configs, array $expectedConfig): void
    {
        $config = (new Processor())->processConfiguration(
            new Configuration(subject: new EasySwooleSymfonyBundle(), container: null, alias: 'easy_swoole'),
            $configs
        );

        self::assertEquals($expectedConfig, $config);
    }
}
