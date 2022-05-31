<?php

declare(strict_types=1);

namespace EonX\EasySwoole\Helpers;

use EonX\EasySwoole\Interfaces\RequestAttributesInterface;
use Symfony\Component\HttpFoundation\Request;

final class EasySwooleEnabledHelper
{
    public static function isNotEnabled(Request $request): bool
    {
        return $request->attributes->getBoolean(RequestAttributesInterface::EASY_SWOOLE_ENABLED) === false;
    }
}
