<?php
declare(strict_types=1);

namespace EonX\EasyServerless\Bundle\CompilerPass;

use EonX\EasyServerless\Aws\Helper\LambdaContextHelper;
use Symfony\Component\DependencyInjection\ChildDefinition;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Exception\InvalidArgumentException;

final class PersistentSystemCacheCompilerPass implements CompilerPassInterface
{
    private const string CACHE_ADAPTER_ARRAY = 'cache.adapter.array';

    private const array SYSTEM_CACHES = [
        'cache.app',
        'cache.system',
    ];

    public function process(ContainerBuilder $container): void
    {
        if (LambdaContextHelper::inLambda() === false && LambdaContextHelper::inLocalLambda() === false) {
            return;
        }

        $systemCacheDefinitions = [];

        foreach (self::SYSTEM_CACHES as $serviceId) {
            if ($container->hasDefinition($serviceId) === false) {
                continue;
            }

            $definition = $container->getDefinition($serviceId);

            if ($definition instanceof ChildDefinition === false) {
                throw new InvalidArgumentException(\sprintf(
                    'For Serverless, "%s" service must be a ChildDefinition, got "%s".',
                    $serviceId,
                    $definition::class
                ));
            }

            if ($definition->getParent() !== self::CACHE_ADAPTER_ARRAY) {
                throw new InvalidArgumentException(\sprintf(
                    'For Serverless, "%s" service must extend "cache.adapter.array", got "%s".',
                    $serviceId,
                    $definition->getParent()
                ));
            }

            $this->removeKernelResetTag($definition);
            $systemCacheDefinitions[] = $definition;
        }

        // Pools with multiple adapters are ChainAdapter definitions and are still reset, as resetting a ChainAdapter
        // resets all its adapters, including the ones not extending a system cache
        $serviceIds = $container->findTaggedServiceIds('cache.pool');
        foreach ($serviceIds as $serviceId => $tags) {
            $definition = $container->getDefinition($serviceId);

            // CachePoolPass does not add the "kernel.reset" tag to abstract pools
            if ($definition->isAbstract()) {
                continue;
            }

            if ($this->extendsSystemCache($container, $definition, $systemCacheDefinitions)) {
                $this->removeKernelResetTag($definition);
            }
        }
    }

    /**
     * @param \Symfony\Component\DependencyInjection\Definition[] $systemCacheDefinitions
     */
    private function extendsSystemCache(
        ContainerBuilder $container,
        Definition $definition,
        array $systemCacheDefinitions,
    ): bool {
        // Walk up all the parents, as CachePoolPass does when adding the "kernel.reset" tag
        while ($definition instanceof ChildDefinition) {
            $definition = $container->findDefinition($definition->getParent());

            if (\in_array($definition, $systemCacheDefinitions, true)) {
                return true;
            }
        }

        return false;
    }

    private function removeKernelResetTag(Definition $definition): void
    {
        $tags = $definition->getTags();

        unset($tags['kernel.reset']);

        $definition->setTags($tags);
    }
}
