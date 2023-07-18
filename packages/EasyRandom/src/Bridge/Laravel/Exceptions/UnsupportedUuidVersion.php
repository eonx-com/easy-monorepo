<?php

declare(strict_types=1);

namespace EonX\EasyRandom\Bridge\Laravel\Exceptions;

use Exception;

final class UnsupportedUuidVersion extends Exception
{
    public static function throw(int $version): never
    {
        throw new self(\sprintf('Unsupported UUID version "%d".', $version));
    }
}
