<?php
declare(strict_types=1);

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

use Symfony\Config\EasyActivityConfig;

return static function (EasyActivityConfig $easyActivityConfig): void {
    $easyActivityConfig
        ->tableName('activity_logs');
};
