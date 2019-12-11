<?php
declare(strict_types=1);

namespace EonX\EasyCfhighlander\Interfaces;

interface ManifestGeneratorInterface
{
    /**
     * Generate manifest for given file statuses.
     *
     * @param string $cwd
     * @param string $version
     * @param \EonX\EasyCfhighlander\File\FileStatus[] $statuses
     *
     * @return void
     */
    public function generate(string $cwd, string $version, array $statuses): void;
}
