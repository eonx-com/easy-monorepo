<?php
declare(strict_types=1);

namespace EonX\EasyCfhighlander\Factories;

use Symfony\Component\Filesystem\Filesystem;

final class FilesystemFactory
{
    /**
     * Create filesystem.
     *
     * @return \Symfony\Component\Filesystem\Filesystem
     */
    public function create(): Filesystem
    {
        return new Filesystem();
    }
}
