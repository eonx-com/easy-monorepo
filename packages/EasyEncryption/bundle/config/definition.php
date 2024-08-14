<?php
declare(strict_types=1);

use Symfony\Component\Config\Definition\Configurator\DefinitionConfigurator;

return static function (DefinitionConfigurator $definition) {
    $definition->rootNode()
        ->children()
            ->scalarNode('default_key_name')->defaultValue('app')->end()
            ->scalarNode('default_encryption_key')->defaultValue('%env(APP_SECRET)%')->end()
            ->scalarNode('default_salt')->defaultNull()->end()
            ->scalarNode('max_chunk_size')->defaultValue(16224)->end()
            ->booleanNode('use_default_key_resolvers')->defaultTrue()->end()
            ->arrayNode('fully_encrypted_messages')
                ->beforeNormalization()->castToArray()->end()
                ->scalarPrototype()->end()
            ->end()
            ->arrayNode('aws_cloud_hsm_encryptor')
                ->addDefaultsIfNotSet()
                ->children()
                    ->booleanNode('enabled')->defaultFalse()->end()
                    ->scalarNode('aad')->defaultValue('')->end()
                    ->arrayNode('sdk_options')
                        ->defaultValue([])
                        ->normalizeKeys(false)
                        ->scalarPrototype()->end()
                    ->end()
                    ->scalarNode('region')->defaultValue('ap-southeast-2')->end()
                    ->scalarNode('role_arn')->defaultNull()->end()
                    ->scalarNode('cluster_id')->defaultNull()->end()
                    ->booleanNode('disable_key_availability_check')->defaultFalse()->end()
                    ->scalarNode('ca_cert_file')->defaultNull()->end()
                    ->scalarNode('ip_address')->defaultNull()->end()
                    ->scalarNode('server_client_cert_file')->defaultNull()->end()
                    ->scalarNode('server_client_key_file')->defaultNull()->end()
                    ->booleanNode('use_aws_cloud_hsm_configure_tool')->defaultTrue()->end()
                    ->scalarNode('user_pin')->defaultNull()->end()
                ->end()
            ->end()
        ->end();
};
