<?php
declare(strict_types=1);

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

use EonX\EasyDecision\Decision\ValueDecision;
use Symfony\Config\EasyDecisionConfig;

return static function (EasyDecisionConfig $easyDecisionConfig): void {
    $easyDecisionConfig->typeMapping([
        'global_event_value_decision' => ValueDecision::class,
    ]);
};
