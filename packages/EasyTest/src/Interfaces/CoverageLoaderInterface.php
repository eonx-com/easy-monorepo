<?php
declare(strict_types=1);

namespace EonX\EasyTest\Interfaces;

interface CoverageLoaderInterface
{
    /**
     * Load coverage output from given path.
     *
     * @param string $path
     *
     * @return string
     *
     * @throws \EonX\EasyTest\Exceptions\UnableToLoadCoverageException
     */
    public function load(string $path): string;
}
