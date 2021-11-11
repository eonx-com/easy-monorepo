<?php

declare(strict_types=1);

namespace EonX\EasyActivity\Bridge\Symfony\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;
use Symfony\Component\Config\Definition\Exception\InvalidTypeException;

final class Configuration implements ConfigurationInterface
{
    public function getConfigTreeBuilder(): TreeBuilder
    {
        $treeBuilder = new TreeBuilder('easy_activity');

        $treeBuilder->getRootNode()
            ->children()
                ->booleanNode('easy_doctrine_subscriber_enabled')
                    ->defaultValue(true)
                    ->info('Whether easy-doctrine subscriber should handle events.')
                ->end()
                ->scalarNode('table_name')
                    ->defaultValue('easy_activity_logs')
                    ->info('The name for table with logs. Defaults to "easy_activity_logs".')
                ->end()
                ->arrayNode('disallowed_properties')
                    ->defaultValue([])
                    ->prototype('scalar')->end()
                    ->info('Property names disallowed to be stored in store.')
                ->end()
                ->arrayNode('subjects')
                    ->arrayPrototype()
                        ->children()
                            ->scalarNode('type')
                                ->info('Subject type. Defaults to short class name of subject.')
                            ->end()
                            ->arrayNode('disallowed_properties')
                                ->defaultValue([])
                                ->info('Property names disallowed to be stored in store.')
                                ->prototype('scalar')->end()
                            ->end()
                            ->arrayNode('allowed_properties')
                                ->defaultValue([])
                                ->info('Property names allowed to be stored in store.')
                                ->prototype('variable')
                                    ->validate()
                                        ->ifArray()
                                        ->then(static function (array $properties) {
                                            foreach ($properties as $value) {
                                                if (\is_array($value) === false) {
                                                    $errorMessage = 'Nested allowed properties should be an array';
                                                    throw new InvalidTypeException($errorMessage);
                                                }
                                            }
                                        })
                                    ->end()
                                ->end()
                            ->end()
                                ->arrayNode('nested_object_allowed_properties')
                                    ->defaultValue([])
                                    ->info('Property names allowed to be stored in store for nested objects.')
                                    ->prototype('variable')
                                    ->validate()
                                        ->always()
                                        ->then(static function ($property) {
                                            if (\is_array($property) === false) {
                                                throw new InvalidTypeException('Property should be an array');
                                            }
                                            foreach ($property as $value) {
                                                if (\is_array($value) === false) {
                                                    $errorMessage = 'Value should be a list of allowed properties';
                                                    throw new InvalidTypeException($errorMessage);
                                                }
                                            }
                                        })
                                    ->end()
                                ->end()
                            ->end()
                        ->end()
                    ->end()
                ->end()
            ->end();

        return $treeBuilder;
    }
}
