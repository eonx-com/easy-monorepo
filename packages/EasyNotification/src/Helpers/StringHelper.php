<?php
declare(strict_types=1);

namespace EonX\EasyNotification\Helpers;

final class StringHelper
{
    public static function ensureEnd(string $subject, string $suffix): string
    {
        return \str_ends_with($subject, $suffix) ? $subject : $subject . $suffix;
    }
}
