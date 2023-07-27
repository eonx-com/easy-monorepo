<?php
declare(strict_types=1);

namespace EonX\EasyTemplatingBlock\Bridge\Symfony\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

final class Configuration implements ConfigurationInterface
{
    public function getConfigTreeBuilder(): TreeBuilder
    {
        $treeBuilder = new TreeBuilder('easy_templating_block');

        $treeBuilder->getRootNode()
            ->children()
                ->booleanNode('is_debug')->defaultValue('%env(bool:APP_DEBUG)%')->end()
                ->booleanNode('use_twig')->defaultTrue()->end()
            ->end();

        return $treeBuilder;
    }
}
