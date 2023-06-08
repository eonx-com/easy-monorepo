<?php

declare(strict_types=1);

namespace EonX\EasySecurity\Bridge\Symfony\DependencyInjection;

use EonX\EasySecurity\Bridge\BridgeConstantsInterface;
use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

final class Configuration implements ConfigurationInterface
{
    public function getConfigTreeBuilder(): TreeBuilder
    {
        $treeBuilder = new TreeBuilder('easy_security');

        $treeBuilder->getRootNode()
            ->children()
                ->booleanNode('easy_bugsnag')->defaultTrue()->end()
                ->scalarNode('token_decoder')->defaultNull()->end()
                ->arrayNode('permissions_locations')
                    ->scalarPrototype()->end()
                    ->beforeNormalization()->castToArray()->end()
                ->end()
                ->arrayNode('roles_locations')
                    ->scalarPrototype()->end()
                    ->beforeNormalization()->castToArray()->end()
                ->end()
                ->arrayNode('voters')
                    ->children()
                        ->integerNode('priority')
                            ->defaultValue(BridgeConstantsInterface::TAG_SECURITY_VOTER_PRIORITY)
                        ->end()
                        ->booleanNode('permission_enabled')->defaultFalse()->end()
                        ->booleanNode('provider_enabled')->defaultFalse()->end()
                        ->booleanNode('role_enabled')->defaultFalse()->end()
                    ->end()
                ->end()
                ->booleanNode('use_default_configurators')->defaultTrue()->end()
            ->end();

        return $treeBuilder;
    }
}
