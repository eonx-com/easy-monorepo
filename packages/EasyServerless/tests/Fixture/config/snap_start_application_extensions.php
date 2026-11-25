<?php
declare(strict_types=1);

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

use EonX\EasyServerless\SnapStart\Enum\SnapStartTag;
use EonX\EasyServerless\Tests\Stub\SnapStart\Hook\AfterRestoreHookStub;
use EonX\EasyServerless\Tests\Stub\SnapStart\Warmer\SnapStartWarmerStub;

return static function (ContainerConfigurator $containerConfigurator): void {
    $containerConfigurator->extension('easy_serverless', [
        'snap_start' => [
            'enabled' => true,
        ],
    ]);

    $services = $containerConfigurator->services();
    $services->defaults()
        ->autowire()
        ->autoconfigure();

    // Tagged through autoconfiguration only, with the default priority 0
    $services->set(SnapStartWarmerStub::class);
    $services->set(AfterRestoreHookStub::class);

    // An explicit priority takes precedence over the tag added by autoconfiguration
    $services
        ->set('app.low_priority_snap_start_warmer', SnapStartWarmerStub::class)
        ->tag(SnapStartTag::Warmer->value, ['priority' => -1000]);
};
