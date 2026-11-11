<?php
declare(strict_types=1);

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

use EonX\EasyLock\Common\Enum\LogLevel;

return static function (ContainerConfigurator $containerConfigurator): void {
    $containerConfigurator->extension('easy_lock', [
        'messenger' => [
            'middleware' => [
                'lock_not_acquired_log_level' => LogLevel::Error,
            ],
        ],
    ]);
};
