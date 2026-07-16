<?php
declare(strict_types=1);

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

use EonX\EasyDecision\Decision\ValueDecision;

return static function (ContainerConfigurator $containerConfigurator): void {
    $containerConfigurator->extension('easy_decision', [
        'type_mapping' => [
            'global_event_value_decision' => ValueDecision::class,
        ],
    ]);
};
