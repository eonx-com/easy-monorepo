<?php
declare(strict_types=1);

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

use Symfony\Config\MonologConfig;

return static function (MonologConfig $monologConfig): void {
    $monologConfig->handler('main')
        ->type('null')
        ->level('debug');
};
