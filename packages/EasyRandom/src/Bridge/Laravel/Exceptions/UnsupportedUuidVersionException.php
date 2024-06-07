<?php
declare(strict_types=1);

namespace EonX\EasyRandom\Bridge\Laravel\Exceptions;

use Exception;

final class UnsupportedUuidVersionException extends Exception
{
    public static function create(int $version, array $supportedUuidVersions): self
    {
        return new self(\sprintf(
            'Unsupported UUID version "%d". Supported versions are: %s',
            $version,
            \implode(', ', $supportedUuidVersions)
        ));
    }
}
