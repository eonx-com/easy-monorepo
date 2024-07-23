<?php
declare(strict_types=1);

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

use Symfony\Config\EasySecurityConfig;

return static function (EasySecurityConfig $securityConfig): void {
    $securityConfig->voters()
        ->permissionEnabled(true);
};
