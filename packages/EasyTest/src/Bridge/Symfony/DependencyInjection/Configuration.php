<?php

declare(strict_types=1);

namespace EonX\EasyTest\Bridge\Symfony\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

final class Configuration implements ConfigurationInterface
{
    public function getConfigTreeBuilder(): TreeBuilder
    {
        $treeBuilder = new TreeBuilder('easy_test');

        $treeBuilder->getRootNode()
            ->children()
                ->booleanNode('enable_message_logger_listener_stub')
                    ->defaultFalse()
                    ->info('Setup MessageLoggerListenerStub service')
                ->end()
            ->end();

        return $treeBuilder;
    }
}
