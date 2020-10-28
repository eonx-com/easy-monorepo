<?php

declare(strict_types=1);

namespace EonX\EasyNotification\Helpers;

use Nette\Utils\Strings;

final class StringHelper
{
    public static function ensureEnd(string $subject, string $suffix): string
    {
        return Strings::endsWith($subject, $suffix) ? $subject : $subject . $suffix;
    }
}
