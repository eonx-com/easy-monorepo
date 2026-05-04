<?php
declare(strict_types=1);

use EonX\EasyEncryption\AwsCloudHsm\Configurator\AwsCloudHsmSdkConfigurator;
use Symfony\Component\Config\Definition\Configurator\DefinitionConfigurator;

return static function (DefinitionConfigurator $definition) {
    $definition->rootNode()
        ->children()
            ->stringNode('default_key_name')->defaultValue('app')->end()
            ->stringNode('default_encryption_key')->defaultValue('%env(APP_SECRET)%')->end()
            ->stringNode('default_salt')->defaultNull()->end()
            ->integerNode('max_chunk_size')->defaultValue(16224)->end()
            ->booleanNode('use_default_key_resolvers')->defaultTrue()->end()
            ->arrayNode('fully_encrypted_messages')
                ->beforeNormalization()->castToArray()->end()
                ->stringPrototype()->end()
            ->end()
            ->arrayNode('aws_cloud_hsm_encryptor')
                ->canBeEnabled()
                ->children()
                    ->stringNode('aad')->defaultValue('')->end()
                    ->arrayNode('sdk_options')
                        ->defaultValue([])
                        ->normalizeKeys(false)
                        ->scalarPrototype()->end()
                    ->end()
                    ->stringNode('region')->defaultValue('ap-southeast-2')->end()
                    ->stringNode('role_arn')->defaultNull()->end()
                    ->stringNode('cluster_id')->defaultNull()->end()
                    ->stringNode('cluster_type')
                        ->defaultValue(AwsCloudHsmSdkConfigurator::SUPPORTED_CLUSTER_TYPES[0])
                        ->info(\sprintf(
                            'Supported types: %s',
                            \implode(', ', AwsCloudHsmSdkConfigurator::SUPPORTED_CLUSTER_TYPES)
                        ))
                    ->end()
                    ->booleanNode('disable_key_availability_check')->defaultFalse()->end()
                    ->stringNode('ca_cert_file')->defaultNull()->end()
                    ->stringNode('ip_address')->defaultNull()->end()
                    ->stringNode('server_client_cert_file')->defaultNull()->end()
                    ->stringNode('server_client_key_file')->defaultNull()->end()
                    ->stringNode('server_port')
                        ->defaultValue(AwsCloudHsmSdkConfigurator::SUPPORTED_SERVER_PORTS[0])
                        ->info(\sprintf(
                            'Supported ports: %s',
                            \implode(', ', AwsCloudHsmSdkConfigurator::SUPPORTED_SERVER_PORTS)
                        ))
                    ->end()
                    ->stringNode('sign_key_name')->defaultValue('app-sign')->end()
                    ->booleanNode('use_aws_cloud_hsm_configure_tool')->defaultTrue()->end()
                    ->stringNode('user_pin')->defaultNull()->end()
                ->end()
            ->end()
        ->end();
};
