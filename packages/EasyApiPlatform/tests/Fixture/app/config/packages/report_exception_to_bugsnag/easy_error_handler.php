<?php
declare(strict_types=1);

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

use Symfony\Config\EasyErrorHandlerConfig;

return static function (EasyErrorHandlerConfig $easyErrorHandlerConfig): void {
    $bugsnagConfig = $easyErrorHandlerConfig->bugsnag();
    $bugsnagConfig->ignoredExceptions([]);
};
