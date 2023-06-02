<?php

declare(strict_types=1);

namespace EonX\EasyCore\Bridge\Symfony\DependencyInjection\Compiler;

use EonX\EasyCore\Bridge\Symfony\ApiPlatform\DataPersister\ChainSimpleDataPersister;
use EonX\EasyCore\Bridge\Symfony\ApiPlatform\DataPersister\TraceableChainSimpleDataPersister;
use EonX\EasyCore\Bridge\Symfony\ApiPlatform\Interfaces\DoctrineOrmDataPersisterInterface;
use EonX\EasyCore\Bridge\Symfony\Interfaces\TagsInterface;
use Symfony\Component\DependencyInjection\Argument\TaggedIteratorArgument;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Reference;

final class ApiPlatformDataPersistersPass implements CompilerPassInterface
{
    /**
     * @var string
     */
    private const CHAIN_SIMPLE_PERSISTER_ID = 'easy_core.chain_simple_data_persister';

    /**
     * @var string
     */
    private const ORIGINAL_DEBUG_PERSISTER_ID = 'debug.api_platform.data_persister';

    /**
     * @var string
     */
    private const ORIGINAL_DOCTRINE_ORM_PERSISTER_ID = 'api_platform.doctrine.orm.data_persister';

    /**
     * @var string
     */
    private const ORIGINAL_PERSISTER_ID = 'api_platform.data_persister';

    /**
     * @var string
     */
    private const ORIGINAL_PERSISTER_TAG = 'api_platform.data_persister';

    /**
     * @throws \ReflectionException
     */
    public function process(ContainerBuilder $container): void
    {
        $this->setDoctrineOrmDataPersisterDefinition($container);
        $this->setSimpleDataPersistersOnChainPersister($container, (bool)$container->getParameter('kernel.debug'));
    }

    /**
     * @return array<string, string>
     *
     * @throws \ReflectionException
     */
    private function getSimplePersisters(ContainerBuilder $container): array
    {
        $persisterIds = \array_keys($container->findTaggedServiceIds(TagsInterface::SIMPLE_DATA_PERSISTER_AUTO_CONFIG));
        $persisters = [];

        foreach ($persisterIds as $persisterId) {
            $def = $container->getDefinition($persisterId);
            $ref = $container->getReflectionClass($def->getClass());

            if ($ref === null) {
                continue;
            }

            // Simple persisters must be public for the chain persister to get them from the container
            $def->setPublic(true);

            /** @var \EonX\EasyCore\Bridge\Symfony\ApiPlatform\Interfaces\SimpleDataPersisterInterface $instance */
            $instance = $ref->newInstanceWithoutConstructor();
            $persisters[$instance->getApiResourceClass()] = $persisterId;
        }

        return $persisters;
    }

    private function setDoctrineOrmDataPersisterDefinition(ContainerBuilder $container): void
    {
        if ($container->hasDefinition(self::ORIGINAL_DOCTRINE_ORM_PERSISTER_ID) === false) {
            return;
        }

        $container->setDefinition(
            self::ORIGINAL_DOCTRINE_ORM_PERSISTER_ID,
            $container->getDefinition(DoctrineOrmDataPersisterInterface::class),
        );
    }

    /**
     * @throws \ReflectionException
     */
    private function setSimpleDataPersistersOnChainPersister(ContainerBuilder $container, bool $debug): void
    {
        if ($container->hasDefinition(self::ORIGINAL_PERSISTER_ID) === false) {
            return;
        }

        $chainSimplePersisterDef = (new Definition(ChainSimpleDataPersister::class))
            ->setAutoconfigured(true)
            ->setAutowired(true)
            ->setArgument('$container', new Reference('service_container'))
            ->setArgument('$simplePersisters', $this->getSimplePersisters($container))
            ->setArgument('$persisters', new TaggedIteratorArgument(self::ORIGINAL_PERSISTER_TAG))
            ->addTag('kernel.reset', ['method' => 'reset']);

        // If not debug, simply set data persister to chain simple one.
        if ($debug === false) {
            $container->setDefinition(self::ORIGINAL_PERSISTER_ID, $chainSimplePersisterDef);

            return;
        }

        // Otherwise decorate the chain simple persister with traceable one
        $container->setDefinition(self::CHAIN_SIMPLE_PERSISTER_ID, $chainSimplePersisterDef);

        $traceablePersisterDef = (new Definition(TraceableChainSimpleDataPersister::class))
            ->setArgument('$decorated', new Reference(self::CHAIN_SIMPLE_PERSISTER_ID))
            ->addTag('kernel.reset', ['method' => 'reset']);

        $container->setDefinition(self::ORIGINAL_PERSISTER_ID, $traceablePersisterDef);
        $container->setDefinition(self::ORIGINAL_DEBUG_PERSISTER_ID, $traceablePersisterDef);
    }
}
