<?php
declare(strict_types=1);

namespace EonX\EasyLock\Bundle\CompilerPass;

use EonX\EasyLock\Bundle\Enum\ConfigParam;
use EonX\EasyLock\Bundle\Enum\ConfigServiceId;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Reference;
use Symfony\Component\Lock\PersistingStoreInterface;
use Symfony\Component\Lock\Store\StoreFactory;

final class RegisterLockStoreServiceCompilerPass implements CompilerPassInterface
{
    private const DEFAULT_CONNECTION_ID = 'flock';

    public function process(ContainerBuilder $container): void
    {
        // If connection from config doesn't exist in container, use flock by default
        $connectionId = $this->getConnectionId(
            $container,
            $this->getParameter($container, ConfigParam::Connection->value)
        );

        $arg = $connectionId !== null ? new Reference($connectionId) : self::DEFAULT_CONNECTION_ID;

        $def = (new Definition(PersistingStoreInterface::class))
            ->setFactory([StoreFactory::class, 'createStore'])
            ->setArguments([$arg]);

        $container->setDefinition(ConfigServiceId::Store->value, $def);
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
