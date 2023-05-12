<?php

declare(strict_types=1);

namespace EonX\EasyTest\Bridge\Symfony;

use EonX\EasyTest\Bridge\Symfony\DependencyInjection\Compiler\AddStubsCompilerPass;
use EonX\EasyTest\Bridge\Symfony\DependencyInjection\EasyTestExtension;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Extension\ExtensionInterface;
use Symfony\Component\HttpKernel\Bundle\Bundle;

final class EasyTestSymfonyBundle extends Bundle
{
    public function build(ContainerBuilder $container): void
    {
        $container->addCompilerPass(new AddStubsCompilerPass());
    }

    public function getContainerExtension(): ExtensionInterface
    {
        return new EasyTestExtension();
    }
}
