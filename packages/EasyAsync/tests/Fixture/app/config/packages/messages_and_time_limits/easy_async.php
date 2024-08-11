<?php
declare(strict_types=1);

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

use Symfony\Config\EasyAsyncConfig;

/**
 * @see \EonX\EasyAsync\Tests\Unit\Bundle\EasyAsyncBundleTest::testMessengerConfigWithMessagesAndTimeLimits
 */
return static function (EasyAsyncConfig $easyAsyncConfig): void {
    $messengerWorkerConfig = $easyAsyncConfig->messenger()
        ->worker();

    $messengerWorkerConfig->stopOnMessagesLimit()
        ->enabled(true)
        ->minMessages(100);

    $messengerWorkerConfig->stopOnTimeLimit()
        ->enabled(true)
        ->minTime(1000);
};
