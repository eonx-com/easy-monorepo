<?php
declare(strict_types=1);

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

/**
 * @see \EonX\EasyAsync\Tests\Unit\Bundle\EasyAsyncBundleTest::testMessengerConfigWithMessagesAndTimeLimits
 */
return App::config([
    'easy_async' => [
        'messenger' => [
            'worker' => [
                'stop_on_messages_limit' => [
                    'enabled' => true,
                    'min_messages' => 100,
                ],
                'stop_on_time_limit' => [
                    'enabled' => true,
                    'min_time' => 1000,
                ],
            ],
        ],
    ],
]);
