<?php
declare(strict_types=1);

namespace EonX\EasyEncryption\Bundle\Enum;

enum ConfigServiceId: string
{
    case DefaultKeyResolver = 'easy_encryption.default_key_resolver';
}
