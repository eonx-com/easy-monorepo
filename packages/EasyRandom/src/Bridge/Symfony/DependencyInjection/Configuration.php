<?php

declare(strict_types=1);

namespace EonX\EasyRandom\Bridge\Symfony\DependencyInjection;

use EonX\EasyRandom\Bridge\BridgeConstantsInterface;
use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

final class Configuration implements ConfigurationInterface
{
    public function getConfigTreeBuilder(): TreeBuilder
    {
        $treeBuilder = new TreeBuilder(BridgeConstantsInterface::EXTENSION_NAME);

        $treeBuilder->getRootNode()
            ->children()
                ->enumNode('default_uuid_version')
                    ->defaultValue(BridgeConstantsInterface::DEFAULT_UUID_VERSION)
                    ->values([4, 6])
                    ->info('The UUID version by default.')
                ->end()
                ->scalarNode('uuid_v4_generator')
                    ->defaultNull()
                    ->info('Service ID of the UUID V4 generator to use.')
                ->end()
                ->scalarNode('uuid_v6_generator')
                    ->defaultNull()
                    ->info('Service ID of the UUID V6 generator to use.')
                ->end()
            ->end();

        return $treeBuilder;
    }
}
