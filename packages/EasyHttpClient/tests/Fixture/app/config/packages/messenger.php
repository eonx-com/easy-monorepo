<?php
declare(strict_types=1);

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

use Symfony\Config\FrameworkConfig;

return static function (FrameworkConfig $frameworkConfig): void {
    $messenger = $frameworkConfig->messenger();
    $messenger->transport('sync')
        ->dsn('sync://');

    $messenger->transport('async')
        ->dsn('in-memory://');

    $messenger->transport('failed')
        ->dsn('in-memory://');
};
