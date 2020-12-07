<?php

declare(strict_types=1);

namespace EonX\EasyLogging\Bridge\Symfony;

use EonX\EasyLogging\Bridge\Symfony\DependencyInjection\Compiler\ReplaceChannelsDefinitionPass;
use Symfony\Component\DependencyInjection\Compiler\PassConfig;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Bundle\Bundle;

final class EasyLoggingBundle extends Bundle
{
    public function build(ContainerBuilder $container): void
    {
        $container->addCompilerPass(new ReplaceChannelsDefinitionPass(), PassConfig::TYPE_BEFORE_OPTIMIZATION, -10);
    }
}
