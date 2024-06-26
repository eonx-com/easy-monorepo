<?php
declare(strict_types=1);

namespace EonX\EasyEncryption\Bundle\Enum;

enum ConfigTag: string
{
    case EncryptionKeyResolver = 'easy_encryption.encryption_key_resolver';
}
