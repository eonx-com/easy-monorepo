<?php
declare(strict_types=1);

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

use InvalidArgumentException;
use Symfony\Config\EasyErrorHandlerConfig;

return static function (EasyErrorHandlerConfig $easyErrorHandlerConfig): void {
    $easyErrorHandlerConfig->logger()
        ->exceptionLoglevels(InvalidArgumentException::class, 200);
};
