<?php
declare(strict_types=1);

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

use Symfony\Config\EasySwooleConfig;

return static function (EasySwooleConfig $easySwooleConfig): void {
    $easySwooleConfig->doctrine()
        ->enabled(false);
};
