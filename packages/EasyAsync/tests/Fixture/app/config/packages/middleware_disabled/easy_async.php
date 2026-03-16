<?php
declare(strict_types=1);

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

use Symfony\Config\EasyAsyncConfig;

/**
 * @see \EonX\EasyAsync\Tests\Unit\Bundle\EasyAsyncBundleTest::testMessengerConfigWithMessengerMiddlewareDisabled
 */
return static function (EasyAsyncConfig $easyAsyncConfig): void {
    $easyAsyncConfig->messenger()
        ->middleware()
        ->enabled(false);
};
