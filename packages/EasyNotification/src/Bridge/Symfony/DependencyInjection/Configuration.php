<?php
declare(strict_types=1);

namespace EonX\EasyNotification\Bridge\Symfony\DependencyInjection;

use EonX\EasyNotification\Bridge\BridgeConstantsInterface;
use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

final class Configuration implements ConfigurationInterface
{
    public function getConfigTreeBuilder(): TreeBuilder
    {
        $treeBuilder = new TreeBuilder(BridgeConstantsInterface::EXTENSION_NAME);

        $treeBuilder->getRootNode()
            ->children()
                ->scalarNode('api_url')->end()
                ->integerNode('config_expires_after')
                    ->defaultValue(BridgeConstantsInterface::CONFIG_CACHE_EXPIRES_AFTER)
                ->end()
            ->end();

        return $treeBuilder;
    }
}
