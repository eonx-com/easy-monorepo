<?php
declare(strict_types=1);

namespace LoyaltyCorp\EasyCfhighlander\Interfaces;

interface ManifestGeneratorInterface
{
    /**
     * Generate manifest for given file statuses.
     *
     * @param string $cwd
     * @param string $version
     * @param \LoyaltyCorp\EasyCfhighlander\File\FileStatus[] $statuses
     *
     * @return void
     */
    public function generate(string $cwd, string $version, array $statuses): void;
}
