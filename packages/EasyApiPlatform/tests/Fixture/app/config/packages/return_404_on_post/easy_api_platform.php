<?php
declare(strict_types=1);

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

use Symfony\Config\EasyApiPlatformConfig;

return static function (EasyApiPlatformConfig $easyApiPlatformConfig): void {
    $easyApiPlatformConfig->return404OnPost()
        ->enabled(false);
};
