<?php

declare(strict_types=1);

namespace EonX\EasyAsync\Bridge\Symfony;

use EonX\EasyAsync\Bridge\Symfony\DependencyInjection\Compiler\RegisterMessengerMiddlewareCompilerPass;
use EonX\EasyAsync\Bridge\Symfony\DependencyInjection\EasyAsyncExtension;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Extension\ExtensionInterface;
use Symfony\Component\HttpKernel\Bundle\Bundle;

final class EasyAsyncSymfonyBundle extends Bundle
{
    public function build(ContainerBuilder $container): void
    {
        // -11 to run after easy-batch pass so middleware are first in the list
        $container->addCompilerPass(new RegisterMessengerMiddlewareCompilerPass(), priority: -11);
    }

    public function getContainerExtension(): ExtensionInterface
    {
        return new EasyAsyncExtension();
    }
}
