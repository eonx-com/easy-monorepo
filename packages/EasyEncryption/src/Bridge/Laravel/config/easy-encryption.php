<?php
declare(strict_types=1);

use EonX\EasyEncryption\Interfaces\EncryptorInterface;

return [
    /**
     * Encryption key associated with default key name.
     */
    'default_encryption_key' => \env('APP_KEY'),

    /**
     * Default key name to use when none provided.
     */
    'default_key_name' => EncryptorInterface::DEFAULT_KEY_NAME,

    /**
     * Optional salt used with default encryption key if provided/needed.
     */
    'default_salt' => \env('APP_SALT'),

    /**
     * Enable key resolve for default encryption key.
     */
    'use_default_key_resolvers' => true,

    'aws_pkcs11_hsm_ca_cert' => '',

    'aws_pkcs11_disable_key_availability_check' => false,

    'aws_pkcs11_hsm_ip_address' => '',

    'aws_pkcs11_cloud_hsm_cluster_id' => '',

    'aws_pkcs11_aws_region' => '',

    'aws_pkcs11_server_client_cert_file' => '',

    'aws_pkcs11_server_client_key_file' => '',

    'aws_pkcs11_cloud_hsm_sdk_options' => [],

    'aws_pkcs11_aws_role_arn' => '',

    'aws_pkcs11_sign_key_name' => '',

    'aws_pkcs11_use_cloud_hsm_configure_tool' => false,

    'aws_pkcs11_user_pin' => '',

    'aws_pkcs11_aad' => '',

    'max_chunk_size' => 16224,

    'aws_pkcs11_encryptor' => [
        'enabled' => false,
    ],
];
