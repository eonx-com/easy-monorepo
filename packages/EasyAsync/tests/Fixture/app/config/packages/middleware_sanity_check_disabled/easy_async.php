<?php
declare(strict_types=1);

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

use Symfony\Config\EasyAsyncConfig;

/**
 * @see \EonX\EasyAsync\Tests\Unit\Bundle\EasyAsyncBundleTest::testMessengerConfigWithDoctrineManagersSanityCheckMiddlewareDisabled
 */
return static function (EasyAsyncConfig $easyAsyncConfig): void {
    $easyAsyncConfig->messenger()
        ->middleware()
        ->doctrineManagersSanityCheck()
        ->enabled(false);
};
