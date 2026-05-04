<?php
declare(strict_types=1);

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

use Symfony\Config\EasyAsyncConfig;

/**
 * @see \EonX\EasyAsync\Tests\Unit\Bundle\EasyAsyncBundleTest::testMessengerConfigWithMessengerMiddlewareDisabledAndChildEnabled
 */
return static function (EasyAsyncConfig $easyAsyncConfig): void {
    $middlewareConfig = $easyAsyncConfig->messenger()
        ->middleware();

    $middlewareConfig->enabled(false);
    $middlewareConfig->doctrineManagersClear()
        ->enabled(true);
};
