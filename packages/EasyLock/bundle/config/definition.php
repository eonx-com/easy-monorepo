<?php
declare(strict_types=1);

use Psr\Log\LogLevel;
use Symfony\Component\Config\Definition\Configurator\DefinitionConfigurator;

return static function (DefinitionConfigurator $definition) {
    $definition->rootNode()
        ->children()
            ->stringNode('connection')->defaultValue('doctrine.dbal.default_connection')->end()
            ->arrayNode('messenger')
                ->addDefaultsIfNotSet()
                ->children()
                    ->arrayNode('middleware')
                        ->canBeDisabled()
                        ->children()
                            // @todo in 7.0 use enumFqcn() with a LogLevel enum (requires symfony/config ^7.3)
                            ->enumNode('lock_not_acquired_log_level')
                                ->info('Log level used when a message is not handled because its lock is not acquired')
                                ->values([
                                    LogLevel::DEBUG,
                                    LogLevel::INFO,
                                    LogLevel::NOTICE,
                                    LogLevel::WARNING,
                                    LogLevel::ERROR,
                                    LogLevel::CRITICAL,
                                    LogLevel::ALERT,
                                    LogLevel::EMERGENCY,
                                ])
                                ->defaultValue(LogLevel::WARNING)
                            ->end()
                        ->end()
                    ->end()
                ->end()
            ->end()
        ->end();
};
