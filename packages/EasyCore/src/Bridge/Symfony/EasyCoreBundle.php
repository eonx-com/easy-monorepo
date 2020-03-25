<?php

declare(strict_types=1);

namespace EonX\EasyCore\Bridge\Symfony;

use EonX\EasyCore\Bridge\Symfony\DependencyInjection\Compiler\AutoConfigureEventListenersPass;
use Symfony\Component\DependencyInjection\Compiler\PassConfig;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Bundle\Bundle;

final class EasyCoreBundle extends Bundle
{
    public function build(ContainerBuilder $container): void
    {
        parent::build($container);

        $container->addCompilerPass(
            new AutoConfigureEventListenersPass(),
            PassConfig::TYPE_BEFORE_OPTIMIZATION,
            10 // To be executed before Doctrine passes
        );
    }
}
