<?php
declare(strict_types=1);

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

use Symfony\Config\EasyRandomConfig;

return static function (EasyRandomConfig $randomConfig): void {
    $randomConfig->uuidVersion(6);
};
