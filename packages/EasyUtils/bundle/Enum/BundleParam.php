<?php
declare(strict_types=1);

namespace EonX\EasyUtils\Bundle\Enum;

enum BundleParam: string
{
    case SensitiveDataDefaultMaskPattern = '*REDACTED*';
}
