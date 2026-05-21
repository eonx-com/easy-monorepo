<?php
declare(strict_types=1);

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

use EonX\EasyServerless\Bundle\Enum\ConfigTag;
use EonX\EasyServerless\Bundle\SqsHandler\SqsHandler;

return static function (ContainerConfigurator $containerConfigurator): void {
    $services = $containerConfigurator->services();

    $services->get(SqsHandler::class)
        ->arg('$stateCheckers', tagged_iterator(ConfigTag::StateChecker->value));
};
