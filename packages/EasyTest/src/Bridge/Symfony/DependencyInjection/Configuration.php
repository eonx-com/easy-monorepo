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
                ->arrayNode('mailer_message_logger_listener_stub')
                    ->addDefaultsIfNotSet()
                    ->info('Setup MailerMessageLoggerListenerStub service for Symfony Mailer')
                    ->children()
                        ->booleanNode('enabled')->defaultFalse()->end()
                ->end()
            ->end();

        return $treeBuilder;
    }
}
