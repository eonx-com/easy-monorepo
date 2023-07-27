<?php
declare(strict_types=1);

namespace EonX\EasyLock\Bridge\Symfony;

use EonX\EasyLock\Bridge\Symfony\DependencyInjection\Compiler\RegisterLockStoreServicePass;
use EonX\EasyLock\Bridge\Symfony\DependencyInjection\Compiler\RegisterMessengerMiddlewareCompilerPass;
use EonX\EasyLock\Bridge\Symfony\DependencyInjection\EasyLockExtension;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Extension\ExtensionInterface;
use Symfony\Component\HttpKernel\Bundle\Bundle;

final class EasyLockSymfonyBundle extends Bundle
{
    public function build(ContainerBuilder $container): void
    {
        $container
            ->addCompilerPass(new RegisterLockStoreServicePass())
            // -9 to run before easy-async and easy-batch so middleware is after
            ->addCompilerPass(new RegisterMessengerMiddlewareCompilerPass(), priority: -9);
    }

    public function getContainerExtension(): ExtensionInterface
    {
        return new EasyLockExtension();
    }
}
