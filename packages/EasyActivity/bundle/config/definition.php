<?php
declare(strict_types=1);

use Symfony\Component\Config\Definition\Configurator\DefinitionConfigurator;
use Symfony\Component\Config\Definition\Exception\InvalidTypeException;

return static function (DefinitionConfigurator $definition) {
    $definition->rootNode()
        ->children()
            ->arrayNode('easy_doctrine')
                ->addDefaultsIfNotSet()
                ->children()
                    ->arrayNode('subscriber')
                        ->canBeDisabled()
                    ->end()
                ->end()
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
            ->arrayNode('fully_serializable_properties')
                ->defaultValue([])
                ->prototype('scalar')->end()
                ->info('Property names that should be fully serialized.')
            ->end()
            ->arrayNode('subjects')
                ->useAttributeAsKey('entity')
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
                            ->info('Property names allowed to be stored in store.')
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
                        ->arrayNode('fully_serializable_properties')
                            ->defaultValue([])
                            ->prototype('scalar')->end()
                            ->info('Property names that should be fully serialized.')
                        ->end()
                        ->arrayNode('nested_object_allowed_properties')
                            ->defaultValue([])
                            ->info('Property names allowed to be stored in store for nested objects.')
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
