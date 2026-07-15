<?php
declare(strict_types=1);

namespace EonX\EasyEncryption\Encryptable\Enum;

enum HashNormalisation: string
{
    case Lowercase = 'lowercase';

    case Trim = 'trim';
}
