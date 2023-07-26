<?php

declare(strict_types=1);

namespace EonX\EasyLock\Bridge\Symfony\DependencyInjection\Compiler;

use EonX\EasyLock\Bridge\BridgeConstantsInterface;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Reference;
use Symfony\Component\Lock\PersistingStoreInterface;
use Symfony\Component\Lock\Store\StoreFactory;

final class RegisterLockStoreServicePass implements CompilerPassInterface
{
    private const DEFAULT_CONNECTION_ID = 'flock';

    public function process(ContainerBuilder $container): void
    {
        // If connection from config doesn't exist in container, use flock by default
        $connectionId = $this->getConnectionId(
            $container,
            $this->getParameter($container, BridgeConstantsInterface::PARAM_CONNECTION)
        );

        $arg = $connectionId !== null ? new Reference($connectionId) : self::DEFAULT_CONNECTION_ID;

        $def = (new Definition(PersistingStoreInterface::class))
            ->setFactory([StoreFactory::class, 'createStore'])
            ->setArguments([$arg]);

        $container->setDefinition(BridgeConstantsInterface::SERVICE_STORE, $def);
    }

    private function getConnectionId(ContainerBuilder $container, ?string $param = null): ?string
    {
        if ($param !== null && ($container->hasAlias($param) || $container->hasDefinition($param))) {
            return $param;
        }

        return null;
    }

    private function getParameter(ContainerBuilder $container, string $param): ?string
    {
        if ($container->hasParameter($param) === false) {
            return null;
        }

        $value = $container->getParameter($param);

        return \is_string($value) ? $value : null;
    }
}
