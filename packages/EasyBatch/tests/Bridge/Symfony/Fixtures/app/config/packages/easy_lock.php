<?php

declare(strict_types=1);

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

use Symfony\Config\EasyLockConfig;

return static function (EasyLockConfig $easyLockConfig): void {
    $easyLockConfig
        ->messengerMiddlewareAutoRegister(true);
};
