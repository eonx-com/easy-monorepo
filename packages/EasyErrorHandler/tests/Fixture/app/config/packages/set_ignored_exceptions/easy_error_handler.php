<?php
declare(strict_types=1);

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

use InvalidArgumentException;
use Monolog\Logger;
use Symfony\Config\EasyErrorHandlerConfig;

return static function (EasyErrorHandlerConfig $easyErrorHandlerConfig): void {
    $easyErrorHandlerConfig->logger()
        ->exceptionLoglevels(InvalidArgumentException::class, Logger::INFO);
};
