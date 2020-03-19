<?php

declare(strict_types=1);

namespace EonX\EasyDocker\Interfaces;

interface ManifestGeneratorInterface
{
    /**
     * @param \EonX\EasyDocker\File\FileStatus[] $statuses
     */
    public function generate(string $cwd, string $version, array $statuses): void;
}
