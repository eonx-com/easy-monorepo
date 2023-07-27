<?php
declare(strict_types=1);

namespace EonX\EasyRandom\Bridge\Symfony\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

final class Configuration implements ConfigurationInterface
{
    public function getConfigTreeBuilder(): TreeBuilder
    {
        $treeBuilder = new TreeBuilder('easy_random');

        $treeBuilder->getRootNode()
            ->children()
                ->integerNode('uuid_version')
                    ->validate()
                        ->ifNotInArray([4, 6])
                        ->thenInvalid('Invalid UUID version %s')
                    ->end()
                    ->defaultValue(6)
                    ->info('Version of UUID to generate')
                ->end()
            ->end();

        return $treeBuilder;
    }
}
