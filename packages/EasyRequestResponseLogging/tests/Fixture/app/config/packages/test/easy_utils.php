<?php
declare(strict_types=1);

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

use Symfony\Config\EasyUtilsConfig;

return static function (EasyUtilsConfig $easyUtilsConfig): void {
    $easyUtilsConfig->sensitiveDataSanitizer()
        ->keysToMask(['payment.secret']);
};
