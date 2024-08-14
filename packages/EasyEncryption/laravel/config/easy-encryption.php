<?php
declare(strict_types=1);

return [
    /**
     * Encryption key associated with default key name.
     */
    'default_encryption_key' => \env('APP_KEY'),

    /**
     * Default key name to use when none provided.
     */
    'default_key_name' => 'app',

    /**
     * Optional salt used with default encryption key if provided/needed.
     */
    'default_salt' => \env('APP_SALT'),

    /**
     * Enable key resolve for default encryption key.
     */
    'use_default_key_resolvers' => true,

    'max_chunk_size' => 16224,

    'aws_cloud_hsm_encryptor' => [
        'enabled' => false,

        'ca_cert_file' => '',

        'disable_key_availability_check' => false,

        'ip_address' => '',

        'cluster_id' => '',

        'region' => '',

        'server_client_cert_file' => '',

        'server_client_key_file' => '',

        'sdk_options' => [],

        'role_arn' => '',

        'use_aws_cloud_hsm_configure_tool' => false,

        'user_pin' => '',

        'aad' => '',
    ],
];
