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
                    ->scalarNode('aad')->defaultValue('')->end()
                    ->arrayNode('aws_cloud_hsm_sdk_options')
                        ->defaultValue([])
                        ->normalizeKeys(false)
                        ->prototype('scalar')->end()
                    ->end()
                    ->scalarNode('aws_region')->defaultValue('ap-southeast-2')->end()
                    ->scalarNode('aws_role_arn')->defaultNull()->end()
                    ->scalarNode('cloud_hsm_cluster_id')->defaultNull()->end()
                    ->booleanNode('disable_key_availability_check')->defaultFalse()->end()
                    ->scalarNode('hsm_ca_cert')->defaultNull()->end()
                    ->arrayNode('hsm_ip_addresses')
                        ->defaultValue([])
                        ->normalizeKeys(false)
                        ->prototype('scalar')->end()
                    ->end()
                    ->scalarNode('hsm_ip_address')
                        ->setDeprecated(
                            'eonx-com/easy-encryption',
                            '5.11.1',
                            'The "%node%" node is deprecated, use "aws_pkcs11_encryptor.hsm_ip_addresses" instead.'
                        )
                        ->defaultNull()
                    ->end()
                    ->scalarNode('server_client_cert_file')->defaultNull()->end()
                    ->scalarNode('server_client_key_file')->defaultNull()->end()
                    ->booleanNode('use_aws_cloud_hsm_configure_tool')->defaultTrue()->end()
                    ->scalarNode('user_pin')->defaultNull()->end()
                ->end()
            ->end()
        ->end();
};
