<?php
declare(strict_types=1);

namespace EonX\EasyServerless\Bundle\CompilerPass;

use Symfony\Component\DependencyInjection\ChildDefinition;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Exception\InvalidArgumentException;

final class PersistentSystemCacheCompilerPass implements CompilerPassInterface
{
    private const CACHE_ADAPTER_ARRAY = 'cache.adapter.array';

    private const SYSTEM_CACHES = [
        'cache.app',
        'cache.system',
    ];

    public function process(ContainerBuilder $container): void
    {
        foreach (self::SYSTEM_CACHES as $serviceId) {
            $definition = $container->getDefinition($serviceId);

            if ($definition instanceof ChildDefinition === false) {
                throw new InvalidArgumentException(\sprintf(
                    'For Serverless, "%s" service must be a ChildDefinition, got "%s".',
                    $serviceId,
                    \get_class($definition)
                ));
            }

            if ($definition->getParent() !== self::CACHE_ADAPTER_ARRAY) {
                throw new InvalidArgumentException(\sprintf(
                    'For Serverless, "%s" service must extend "cache.adapter.array", got "%s".',
                    $serviceId,
                    $definition->getParent()
                ));
            }
        }

        $serviceIds = $container->findTaggedServiceIds('cache.pool');
        foreach ($serviceIds as $serviceId => $tags) {
            /** @var \Symfony\Component\DependencyInjection\ChildDefinition $definition */
            $definition = $container->getDefinition($serviceId);

            if ($definition instanceof ChildDefinition === false) {
                continue;
            }

            if (\in_array($definition->getParent(), self::SYSTEM_CACHES, true)) {
                $tags = $definition->getTags();

                unset($tags['kernel.reset']);

                $definition->setTags($tags);
            }
        }
    }
}
