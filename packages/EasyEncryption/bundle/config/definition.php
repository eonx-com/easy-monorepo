<?php
declare(strict_types=1);

use EonX\EasyEncryption\AwsCloudHsm\Configurator\AwsCloudHsmSdkConfigurator;
use EonX\EasyEncryption\Encryptable\Enum\HashNormalization;
use Symfony\Component\Config\Definition\Configurator\DefinitionConfigurator;

return static function (DefinitionConfigurator $definition) {
    $definition->rootNode()
        ->children()
            ->stringNode('default_key_name')->defaultValue('app')->end()
            ->stringNode('default_encryption_key')->defaultValue('%env(APP_SECRET)%')->end()
            ->stringNode('default_salt')->defaultNull()->end()
            ->integerNode('max_chunk_size')->defaultValue(16224)->end()
            ->booleanNode('use_default_key_resolvers')->defaultTrue()->end()
            ->arrayNode('default_hash_normalizations')
                ->info('Normalization(s) applied to an encryptable field value before it is hashed.')
                ->beforeNormalization()->castToArray()->end()
                ->defaultValue([HashNormalization::Lowercase->value])
                ->enumPrototype()
                    ->values(\array_map(
                        static fn(HashNormalization $case): string => $case->value,
                        HashNormalization::cases()
                    ))
                ->end()
            ->end()
            ->arrayNode('fully_encrypted_messages')
                ->beforeNormalization()->castToArray()->end()
                ->stringPrototype()->end()
            ->end()
            ->arrayNode('messenger')
                ->addDefaultsIfNotSet()
                ->children()
                    ->arrayNode('signing_keys')
                        ->info(
                            'Keys used to sign Messenger envelopes. The first key signs; every key verifies, so a '
                            . 'rotated-out key kept last keeps accepting messages queued before the rotation until '
                            . 'they drain. Defaults to the package encryption key.'
                        )
                        ->beforeNormalization()->castToArray()->end()
                        ->scalarPrototype()->end()
                    ->end()
                    ->booleanNode('allow_unsigned_messages')
                        ->defaultTrue()
                        ->info(
                            'Accept Messenger envelopes without a valid HMAC signature. Defaults to true during the '
                            . 'signing roll-out so pre-upgrade messages keep decoding; set to false once every queue '
                            . 'holds only signed messages to fully close the legacy native-unserialize path. A future '
                            . 'major will default this to false.'
                        )
                    ->end()
                ->end()
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
