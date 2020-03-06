<?php
declare(strict_types=1);

namespace EonX\EasyTest\Coverage\Loaders;

use EonX\EasyTest\Exceptions\UnableToLoadCoverageException;
use EonX\EasyTest\Interfaces\CoverageLoaderInterface;

final class FilesystemCoverageLoader implements CoverageLoaderInterface
{
    public function load(string $path): string
    {
        if (\file_exists($path)) {
            return \file_get_contents($path) ?: '';
        }

        throw new UnableToLoadCoverageException(\sprintf('[%s] Given path "%s" not found', static::class, $path));
    }
}
