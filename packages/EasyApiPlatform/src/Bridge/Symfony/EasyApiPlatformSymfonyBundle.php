<?php

declare(strict_types=1);

namespace EonX\EasyApiPlatform\Bridge\Symfony;

use EonX\EasyApiPlatform\Bridge\Symfony\DependencyInjection\Compiler\EasyCoreCompilerPass;
use EonX\EasyApiPlatform\Bridge\Symfony\DependencyInjection\EasyApiPlatformExtension;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Extension\ExtensionInterface;
use Symfony\Component\HttpKernel\Bundle\Bundle;

final class EasyApiPlatformSymfonyBundle extends Bundle
{
    public function build(ContainerBuilder $container): void
    {
        parent::build($container);

        $container->addCompilerPass(new EasyCoreCompilerPass());
    }

    public function getContainerExtension(): ExtensionInterface
    {
        return new EasyApiPlatformExtension();
    }
}
