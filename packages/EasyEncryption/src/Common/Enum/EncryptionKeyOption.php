<?php
declare(strict_types=1);

namespace EonX\EasyEncryption\Common\Enum;

enum EncryptionKeyOption: string
{
    case Key = 'key';

    case PublicKey = 'public_key';

    case Salt = 'salt';

    case SecretKey = 'secret_key';
}
