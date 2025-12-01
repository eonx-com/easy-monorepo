<?php
declare(strict_types=1);

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

use Symfony\Config\EasyRequestIdConfig;

return static function (EasyRequestIdConfig $easyRequestIdConfig): void {
    $easyRequestIdConfig->easyErrorHandler()->enabled(false);
};
