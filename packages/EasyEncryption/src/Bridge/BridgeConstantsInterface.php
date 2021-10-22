<?php

declare(strict_types=1);

namespace EonX\EasyEncryption\Bridge;

interface BridgeConstantsInterface
{
    /**
     * @var string
     */
    public const PARAM_DEFAULT_KEY_NAME = 'easy_encryption.default_key_name';

    /**
     * @var string
     */
    public const PARAM_DEFAULT_ENCRYPTION_KEY = 'easy_encryption.default_encryption_key';

    /**
     * @var string
     */
    public const PARAM_DEFAULT_SALT = 'easy_encryption.default_salt';

    /**
     * @var string
     */
    public const SERVICE_DEFAULT_KEY_RESOLVER = 'easy_encryption.default_key_resolver';

    /**
     * @var string
     */
    public const TAG_ENCRYPTION_KEY_RESOLVER = 'easy_encryption.encryption_key_resolver';
}
