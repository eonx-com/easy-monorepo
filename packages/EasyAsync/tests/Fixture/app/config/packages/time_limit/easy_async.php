<?php
declare(strict_types=1);

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

use Symfony\Config\EasyAsyncConfig;

/**
 * @see \EonX\EasyAsync\Tests\Unit\Bundle\EasyAsyncBundleTest::testMessengerConfigWithTimeLimit
 */
return static function (EasyAsyncConfig $easyAsyncConfig): void {
    $messengerWorkerConfig = $easyAsyncConfig->messengerWorker();

    $messengerWorkerConfig->stopOnTimeLimit()
        ->enabled(true)
        ->minTime(1000);
};
