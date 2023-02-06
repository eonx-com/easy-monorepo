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
                ->booleanNode('mailer_message_logger_listener_stub_enabled')
                    ->defaultFalse()
                    ->info('Setup MailerMessageLoggerListenerStub service for Symfony Mailer')
                ->end()
            ->end();

        return $treeBuilder;
    }
}
