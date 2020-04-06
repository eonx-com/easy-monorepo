<?php

declare(strict_types=1);

namespace EonX\EasySsm\Services\Hash;

use Nette\Utils\FileSystem as NetterFileSystem;
use Symfony\Component\Filesystem\Filesystem;

final class HashRepository implements HashRepositoryInterface
{
    /**
     * @var \Symfony\Component\Filesystem\Filesystem
     */
    private $filesystem;

    public function __construct(Filesystem $filesystem)
    {
        $this->filesystem = $filesystem;
    }

    public function get(string $name): ?string
    {
        $filename = $this->getFilename($name);

        if ($this->filesystem->exists($filename) === false) {
            return null;
        }

        return NetterFileSystem::read($filename);
    }

    public function save(string $name, string $hash): void
    {
        $this->filesystem->dumpFile($this->getFilename($name), $hash);
    }

    private function getFilename(string $name): string
    {
        return \sprintf('%s/../../../var/%s.hash', __DIR__, $name);
    }
}
