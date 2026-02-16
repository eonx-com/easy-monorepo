<?php
declare(strict_types=1);

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

/**
 * @see \EonX\EasyAsync\Tests\Unit\Bundle\EasyAsyncBundleTest::testMessengerConfigWithMessagesLimit
 */
return App::config([
    'easy_async' => [
        'messenger' => [
            'worker' => [
                'stop_on_messages_limit' => [
                    'enabled' => true,
                    'min_messages' => 100,
                ],
            ],
        ],
    ],
]);
