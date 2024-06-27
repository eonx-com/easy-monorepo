<?php
declare(strict_types=1);

namespace EonX\EasySwoole\Common\Helper;

use EonX\EasySwoole\Common\Enum\RequestAttribute;
use Symfony\Component\HttpFoundation\Request;

final class EasySwooleEnabledHelper
{
    public static function isNotEnabled(Request $request): bool
    {
        return $request->attributes->getBoolean(RequestAttribute::EasySwooleEnabled->value) === false;
    }
}
