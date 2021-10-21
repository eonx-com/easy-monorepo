<?php

declare(strict_types=1);

namespace EonX\EasyEncryption\Bridge\Symfony\DependencyInjection;

use EonX\EasyEncryption\Interfaces\EncryptorInterface;
use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

final class Configuration implements ConfigurationInterface
{
    public function getConfigTreeBuilder(): TreeBuilder
    {
        $treeBuilder = new TreeBuilder('easy_encryption');

        $treeBuilder->getRootNode()
            ->children()
                ->scalarNode('default_key_name')->defaultValue(EncryptorInterface::DEFAULT_KEY_NAME)->end()
                ->scalarNode('default_encryption_key')->defaultValue('%env(APP_SECRET)%')->end()
                ->scalarNode('default_salt')->defaultNull()->end()
                ->booleanNode('use_default_key_resolvers')->defaultTrue()->end()
            ->end();

        return $treeBuilder;
    }
}
