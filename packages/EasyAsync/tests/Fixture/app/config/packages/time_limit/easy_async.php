<?php
declare(strict_types=1);

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

/**
 * @see \EonX\EasyAsync\Tests\Unit\Bundle\EasyAsyncBundleTest::testMessengerConfigWithTimeLimit
 */
return App::config([
    'easy_async' => [
        'messenger' => [
            'worker' => [
                'stop_on_time_limit' => [
                    'enabled' => true,
                    'min_time' => 1000,
                ],
            ],
        ],
    ],
]);
