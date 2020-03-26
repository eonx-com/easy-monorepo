<?php
declare(strict_types=1);

namespace EonX\EasyCore\Bridge\Symfony\DependencyInjection\Compiler;

use EonX\EasyCore\Bridge\Symfony\ApiPlatform\DataPersister\ChainSimpleDataPersister;
use EonX\EasyCore\Bridge\Symfony\Interfaces\TagsInterface;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;

final class ApiPlatformSimpleDataPersisterPass implements CompilerPassInterface
{
    public function process(ContainerBuilder $container): void
    {
        if ($container->has(ChainSimpleDataPersister::class) === false) {
            return;
        }

        $persisterIds = \array_keys($container->findTaggedServiceIds(TagsInterface::SIMPLE_DATA_PERSISTER_AUTO_CONFIG));
        $persisters = [];

        foreach ($persisterIds as $persisterId) {
            $def = $container->getDefinition($persisterId);

            // Simple persisters must be public for the chain persister to get them from the container
            $def->setPublic(true);

            /** @var \EonX\EasyCore\Bridge\Symfony\ApiPlatform\Interfaces\SimpleDataPersisterInterface $instance */
            $instance = $container->getReflectionClass($def->getClass())->newInstanceWithoutConstructor();

            $persisters[$instance->getApiResourceClass()] = $persisterId;
        }

        $container->getDefinition(ChainSimpleDataPersister::class)->replaceArgument(2, $persisters);
    }
}
