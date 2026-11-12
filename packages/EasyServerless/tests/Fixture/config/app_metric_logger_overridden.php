<?php
declare(strict_types=1);

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

use EonX\EasyServerless\Bundle\Enum\ConfigServiceId;
use Psr\Log\NullLogger;

return static function (ContainerConfigurator $containerConfigurator): void {
    $containerConfigurator->services()
        ->set(ConfigServiceId::AppMetricLogger->value, NullLogger::class);
};
