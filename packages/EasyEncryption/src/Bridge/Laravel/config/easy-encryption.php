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
];
