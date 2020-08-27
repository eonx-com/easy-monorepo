<?php

declare(strict_types=1);

namespace EonX\EasyBugsnag\Bridge\Symfony\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

final class Configuration implements ConfigurationInterface
{
    public function getConfigTreeBuilder(): TreeBuilder
    {
        $treeBuilder = new TreeBuilder('easy_bugsnag');

        $treeBuilder->getRootNode()
            ->children()
                ->scalarNode('api_key')->isRequired()->end()
                ->booleanNode('doctrine_om')->defaultFalse()->end()
            ->end();

        return $treeBuilder;
    }
}
