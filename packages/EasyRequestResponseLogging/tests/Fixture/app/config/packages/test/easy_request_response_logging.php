<?php
declare(strict_types=1);

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

use Symfony\Config\EasyRequestResponseLoggingConfig;

return static function (EasyRequestResponseLoggingConfig $easyRequestResponseLoggingConfig): void {
    $easyRequestResponseLoggingConfig
        ->enabled(true)
        ->maxBodySize(1024)
        ->maxRawBodySize(4096);
};
