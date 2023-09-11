<?php
declare(strict_types=1);

use EonX\EasyEncryption\Interfaces\EncryptorInterface;
use Symfony\Component\Config\Definition\Configurator\DefinitionConfigurator;

return static function (DefinitionConfigurator $definition) {
    $definition->rootNode()
        ->children()
            ->scalarNode('default_key_name')->defaultValue(EncryptorInterface::DEFAULT_KEY_NAME)->end()
            ->scalarNode('default_encryption_key')->defaultValue('%env(APP_SECRET)%')->end()
            ->scalarNode('default_salt')->defaultNull()->end()
            ->booleanNode('use_default_key_resolvers')->defaultTrue()->end()
            ->arrayNode('aws_pkcs11_encryptor')
                ->addDefaultsIfNotSet()
                ->children()
                    ->booleanNode('enabled')->defaultFalse()->end()
                    ->scalarNode('user_pin')->defaultNull()->end()
                    ->scalarNode('hsm_ca_cert')->defaultNull()->end()
                    ->booleanNode('disable_key_availability_check')->defaultFalse()->end()
                    ->scalarNode('hsm_ip_address')->defaultNull()->end()
                    ->scalarNode('cloud_hsm_cluster_id')->defaultNull()->end()
                    ->scalarNode('aws_region')->defaultValue('ap-southeast-2')->end()
                    ->scalarNode('aad')->defaultValue('')->end()
                    ->scalarNode('server_client_cert_file')->defaultNull()->end()
                    ->scalarNode('server_client_key_file')->defaultNull()->end()
                    ->arrayNode('aws_cloud_hsm_sdk_options')
                        ->defaultValue([])
                        ->prototype('scalar')->end()
                    ->end()
                ->end()
            ->end()
        ->end();
};
