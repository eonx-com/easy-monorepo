<?php
declare(strict_types=1);

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

use Symfony\Config\FrameworkConfig;

return static function (FrameworkConfig $frameworkConfig): void {
    $frameworkConfig
        ->test(true);
};
