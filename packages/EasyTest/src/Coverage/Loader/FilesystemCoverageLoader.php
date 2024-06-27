<?php
declare(strict_types=1);

namespace EonX\EasyTest\Coverage\Loader;

use EonX\EasyTest\Coverage\Exception\UnableToLoadCoverageException;

final class FilesystemCoverageLoader implements CoverageLoaderInterface
{
    public function load(string $path): string
    {
        if (\file_exists($path)) {
            return \file_get_contents($path) ?: '';
        }

        throw new UnableToLoadCoverageException(\sprintf('[%s] Given path "%s" not found', self::class, $path));
    }
}
