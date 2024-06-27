<?php
declare(strict_types=1);

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

use Symfony\Config\EasyDecisionConfig;

return static function (EasyDecisionConfig $easyDecisionConfig): void {
    $easyDecisionConfig->useExpressionLanguage(false);
};
