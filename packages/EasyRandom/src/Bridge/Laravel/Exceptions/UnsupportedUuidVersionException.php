<?php

declare(strict_types=1);

namespace EonX\EasyRandom\Bridge\Laravel\Exceptions;

use Exception;

final class UnsupportedUuidVersionException extends Exception
{
    public static function create(int $version): self
    {
        return new self(\sprintf('Unsupported UUID version "%d".', $version));
    }
}
