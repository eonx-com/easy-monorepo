<?php

declare(strict_types=1);

namespace EonX\EasyCore\Bridge\Symfony\DependencyInjection\Compiler;

use EonX\EasyCore\Bridge\Symfony\ApiPlatform\DataPersister\ChainSimpleDataPersister;
use EonX\EasyCore\Bridge\Symfony\ApiPlatform\Interfaces\DoctrineOrmDataPersisterInterface;
use EonX\EasyCore\Bridge\Symfony\Interfaces\TagsInterface;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;

final class ApiPlatformSimpleDataPersisterPass implements CompilerPassInterface
{
    public function process(ContainerBuilder $container): void
    {
        $originalId = 'api_platform.data_persister';
        $originalDoctrineOrm = 'api_platform.doctrine.orm.data_persister';

        if ($container->hasDefinition($originalId) === false
            || $container->getDefinition($originalId)->getClass() !== ChainSimpleDataPersister::class) {
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

        $coreDef = $container->getDefinition($originalId);
        $coreDef->replaceArgument(2, $persisters);

        if ($container->hasDefinition($originalDoctrineOrm) === false) {
            return;
        }

        $container->setAlias(DoctrineOrmDataPersisterInterface::class, $originalDoctrineOrm);
    }
}
