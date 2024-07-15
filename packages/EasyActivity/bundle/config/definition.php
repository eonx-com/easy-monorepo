<?php
declare(strict_types=1);

use Symfony\Component\Config\Definition\Configurator\DefinitionConfigurator;
use Symfony\Component\Config\Definition\Exception\InvalidTypeException;

return static function (DefinitionConfigurator $definition) {
    $definition->rootNode()
        ->children()
            ->booleanNode('easy_doctrine_subscriber_enabled')
                ->defaultValue(true)
                ->info('Whether easy-doctrine subscriber should handle events.')
            ->end()
            ->scalarNode('table_name')
                ->defaultValue('easy_activity_logs')
                ->info('Table name for storing activity log entries.')
            ->end()
            ->arrayNode('disallowed_properties')
                ->defaultValue([])
                ->prototype('scalar')->end()
                ->info('An optional array of subject property names to be excluded from activity log entries'
                . ' globally (i.e. the list will be applied to all subjects defined in the `subjects` configuration'
                . ' option).')
            ->end()
            ->arrayNode('subjects')
                ->info('A set of subject classes to be logged.')
                ->useAttributeAsKey('entity')
                ->arrayPrototype()
                    ->children()
                        ->scalarNode('type')
                            ->info('An optional subject type mapping. If no type is provided, a short class name'
                            . ' will be used by default.')
                        ->end()
                        ->arrayNode('disallowed_properties')
                            ->defaultValue([])
                            ->info('An optional array of subject property names to be excluded from activity log'
                            . ' entries for the relevant subject.')
                            ->prototype('scalar')->end()
                        ->end()
                        ->arrayNode('allowed_properties')
                            ->info('An optional array of subject property names to be allowed for activity log'
                            . ' entries. If the option is present, only the specified properties will be included in'
                            . ' activity log entries for the relevant subject.')
                            ->defaultValue([])
                            ->beforeNormalization()
                                ->ifArray()
                                ->then(static function (array $properties) {
                                    $result = [];
                                    foreach ($properties as $key => $property) {
                                        if (\is_array($property)) {
                                            $key = \array_key_first($property);
                                            $property = \reset($property);
                                        }

                                        $result[$key] = $property;
                                    }

                                    return $result;
                                })
                            ->end()
                            ->validate()
                                ->ifArray()
                                ->then(static function (array $properties) {
                                    foreach ($properties as $key => $property) {
                                        if (\is_string($key) === true && \is_array($property) === false) {
                                            $errorMessage = 'Value of named property should be an array type.';

                                            throw new InvalidTypeException($errorMessage);
                                        }
                                    }

                                    return $properties;
                                })
                            ->end()
                            ->prototype('variable')->end()
                        ->end()
                        ->arrayNode('nested_object_allowed_properties')
                            ->defaultValue([])
                            ->info('By default, nested objects within a subject only contain the `id` key.'
                            . ' You can specify an optional set of classes that describe nested objects within the'
                            . ' subject, each containing an array of property names to be included for activity log'
                            . ' entries.')
                            ->prototype('variable')
                                ->validate()
                                    ->always()
                                    ->then(static function ($property) {
                                        if (\is_array($property) === false) {
                                            $errorMessage = 'Property should be an array type';

                                            throw new InvalidTypeException($errorMessage);
                                        }

                                        return $property;
                                    })
                                ->end()
                            ->end()
                        ->end()
                    ->end()
                ->end()
            ->end()
        ->end()
    ->end();
};
