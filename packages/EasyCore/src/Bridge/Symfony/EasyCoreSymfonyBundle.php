<?php

declare(strict_types=1);

namespace EonX\EasyCore\Bridge\Symfony;

use EonX\EasyCore\Bridge\Symfony\DependencyInjection\Compiler\AutoConfigureEventListenersPass;
use EonX\EasyCore\Bridge\Symfony\DependencyInjection\Compiler\ReplaceProfilerStoragePass;
use EonX\EasyCore\Bridge\Symfony\DependencyInjection\EasyCoreExtension;
use Symfony\Component\DependencyInjection\Compiler\PassConfig;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Extension\ExtensionInterface;
use Symfony\Component\HttpKernel\Bundle\Bundle;

final class EasyCoreSymfonyBundle extends Bundle
{
    public function build(ContainerBuilder $container): void
    {
        parent::build($container);

        // To be executed before Doctrine passes
        $container->addCompilerPass(new AutoConfigureEventListenersPass(), PassConfig::TYPE_BEFORE_OPTIMIZATION, 10);
        $container->addCompilerPass(new ReplaceProfilerStoragePass());
    }

    public function getContainerExtension(): ExtensionInterface
    {
        return new EasyCoreExtension();
    }
}
