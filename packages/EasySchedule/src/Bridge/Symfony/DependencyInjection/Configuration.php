<?php
declare(strict_types=1);

namespace EonX\EasySchedule\Bridge\Symfony\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

final class Configuration implements ConfigurationInterface
{
    public function getConfigTreeBuilder(): TreeBuilder
    {
        $treeBuilder = new TreeBuilder('easy_schedule');

        $treeBuilder->getRootNode()
            ->children()
                ->booleanNode('clear_entity_manager_on_command_execution')->defaultFalse()->end()
            ->end();

        return $treeBuilder;
    }
}
