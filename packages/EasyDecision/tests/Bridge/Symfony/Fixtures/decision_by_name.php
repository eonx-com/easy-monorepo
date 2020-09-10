<?php

declare(strict_types=1);

use EonX\EasyDecision\Decisions\ValueDecision;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

return static function (ContainerConfigurator $containerConfigurator): void {
    $containerConfigurator->extension('easy_decision', [
        'type_mapping' => [
            'global_event_value_decision' => ValueDecision::class,
        ],
    ]);
};
