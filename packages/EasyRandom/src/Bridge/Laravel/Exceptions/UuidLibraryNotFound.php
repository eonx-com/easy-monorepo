<?php

declare(strict_types=1);

namespace EonX\EasyRandom\Bridge\Laravel\Exceptions;

use Exception;

final class UuidLibraryNotFound extends Exception
{
    public static function throw(): never
    {
        throw new self('UUID library not found. Please install "symfony/uid" or "ramsey/uuid".');
    }
}
