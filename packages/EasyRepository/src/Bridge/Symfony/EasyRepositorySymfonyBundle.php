<?php

declare(strict_types=1);

namespace EonX\EasyRepository\Bridge\Symfony;

use EonX\EasyRepository\Bridge\Symfony\DependencyInjection\Compiler\SetPaginationOnRepositoryPass;
use EonX\EasyRepository\Bridge\Symfony\DependencyInjection\EasyRepositoryExtension;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Extension\ExtensionInterface;
use Symfony\Component\HttpKernel\Bundle\Bundle;

final class EasyRepositorySymfonyBundle extends Bundle
{
    public function build(ContainerBuilder $container): void
    {
        $container->addCompilerPass(new SetPaginationOnRepositoryPass());
    }

    public function getContainerExtension(): ExtensionInterface
    {
        return new EasyRepositoryExtension();
    }
}
