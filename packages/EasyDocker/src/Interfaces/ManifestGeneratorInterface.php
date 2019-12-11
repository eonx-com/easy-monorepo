<?php
declare(strict_types=1);

namespace EonX\EasyDocker\Interfaces;

interface ManifestGeneratorInterface
{
    /**
     * Generate manifest for given file statuses.
     *
     * @param string $cwd
     * @param string $version
     * @param \EonX\EasyDocker\File\FileStatus[] $statuses
     *
     * @return void
     */
    public function generate(string $cwd, string $version, array $statuses): void;
}
