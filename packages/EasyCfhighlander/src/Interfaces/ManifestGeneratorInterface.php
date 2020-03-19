<?php

declare(strict_types=1);

namespace EonX\EasyCfhighlander\Interfaces;

interface ManifestGeneratorInterface
{
    /**
     * @param \EonX\EasyCfhighlander\File\FileStatus[] $statuses
     */
    public function generate(string $cwd, string $version, array $statuses): void;
}
