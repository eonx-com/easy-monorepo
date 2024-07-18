<?php
declare(strict_types=1);

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

use Symfony\Config\EasyAsyncConfig;

/**
 * @see \EonX\EasyAsync\Tests\Application\Bundle\EasyAsyncBundleTest::testMessengerConfigWithMessagesAndTimeLimits
 */
return static function (EasyAsyncConfig $easyAsyncConfig): void {
    $messengerWorkerConfig = $easyAsyncConfig->messengerWorker();

    $messengerWorkerConfig->stopOnMessagesLimit()
        ->enabled(true)
        ->minMessages(100);

    $messengerWorkerConfig->stopOnTimeLimit()
        ->enabled(true)
        ->minTime(1000);
};
