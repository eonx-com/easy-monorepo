<?php
declare(strict_types=1);

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

use Symfony\Config\EasyDoctrineConfig;

return static function (EasyDoctrineConfig $easyDoctrineConfig): void {
    $awsRdsConfig = $easyDoctrineConfig->awsRds();

    $awsRdsConfig->iam()
        ->enabled(true);

    $awsRdsConfig->ssl()
        ->enabled(true);
};
