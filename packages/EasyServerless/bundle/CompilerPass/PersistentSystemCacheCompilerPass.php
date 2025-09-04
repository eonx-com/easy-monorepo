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
    private const CACHE_ADAPTER_ARRAY = 'cache.adapter.array';

    private const SYSTEM_CACHES = [
        'cache.app',
        'cache.system',
    ];

    public function process(ContainerBuilder $container): void
    {
        if (LambdaContextHelper::inLambda() === false && LambdaContextHelper::inLocalLambda() === false) {
            return;
        }

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
        }

        $serviceIds = $container->findTaggedServiceIds('cache.pool');
        foreach ($serviceIds as $serviceId => $tags) {
            $definition = $container->getDefinition($serviceId);

            if ($definition instanceof ChildDefinition === false) {
                continue;
            }

            if (\in_array($definition->getParent(), self::SYSTEM_CACHES, true)) {
                $this->removeKernelResetTag($definition);
            }
        }
    }

    private function removeKernelResetTag(Definition $definition): void
    {
        $tags = $definition->getTags();

        unset($tags['kernel.reset']);

        $definition->setTags($tags);
    }
}
