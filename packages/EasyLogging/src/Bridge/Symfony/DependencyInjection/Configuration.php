<?php
declare(strict_types=1);

namespace EonX\EasyLogging\Bridge\Symfony\DependencyInjection;

use EonX\EasyLogging\Interfaces\LoggerFactoryInterface;
use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

final class Configuration implements ConfigurationInterface
{
    public function getConfigTreeBuilder(): TreeBuilder
    {
        $treeBuilder = new TreeBuilder('easy_logging');

        $treeBuilder->getRootNode()
            ->children()
                ->scalarNode('default_channel')->defaultValue(LoggerFactoryInterface::DEFAULT_CHANNEL)->end()
                ->arrayNode('sensitive_data_sanitizer')
                    ->addDefaultsIfNotSet()
                    ->children()
                        ->booleanNode('enabled')->defaultFalse()->end()
                    ->end()
                ->end()
                ->booleanNode('stream_handler')->defaultTrue()->end()
                ->integerNode('stream_handler_level')->defaultNull()->end()
            ->end();

        return $treeBuilder;
    }
}
