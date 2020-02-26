<?php
declare(strict_types=1);

namespace EonX\EasyAsync\Bridge\Symfony\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

final class Configuration implements ConfigurationInterface
{
    /**
     * Get config tree builder.
     *
     * @return \Symfony\Component\Config\Definition\Builder\TreeBuilder
     */
    public function getConfigTreeBuilder(): TreeBuilder
    {
        $treeBuilder = new TreeBuilder('easy_async');

        $treeBuilder->getRootNode()
            ->children()
                ->scalarNode('implementation')->defaultValue('doctrine')->end()
                ->scalarNode('jobs_table')->defaultValue('easy_async_jobs')->end()
                ->scalarNode('job_logs_table')->defaultValue('easy_async_job_logs')->end()
            ->end();

        return $treeBuilder;
    }
}
