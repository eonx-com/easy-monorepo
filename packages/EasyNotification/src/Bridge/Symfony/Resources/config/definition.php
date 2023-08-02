<?php
declare(strict_types=1);

use EonX\EasyNotification\Bridge\BridgeConstantsInterface;
use Symfony\Component\Config\Definition\Configurator\DefinitionConfigurator;

return static function (DefinitionConfigurator $definition) {
    $definition->rootNode()
        ->children()
            ->scalarNode('api_url')->end()
            ->integerNode('config_expires_after')
                ->defaultValue(BridgeConstantsInterface::CONFIG_CACHE_EXPIRES_AFTER)
            ->end()
        ->end();
};
