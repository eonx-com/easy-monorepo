<?php
declare(strict_types=1);

namespace EonX\EasyCfhighlander\File;

use EonX\EasyCfhighlander\Interfaces\FileGeneratorInterface;
use EonX\EasyCfhighlander\Interfaces\ManifestGeneratorInterface;
use Symfony\Component\Filesystem\Filesystem;

final class ManifestGenerator implements ManifestGeneratorInterface
{
    /**
     * @var string
     */
    public const MANIFEST_NAME = 'easy-cfhighlander-manifest.json';

    /**
     * @var \Symfony\Component\Filesystem\Filesystem
     */
    private $filesystem;

    public function __construct(Filesystem $filesystem)
    {
        $this->filesystem = $filesystem;
    }

    /**
     * @param \EonX\EasyCfhighlander\File\FileStatus[] $statuses
     */
    public function generate(string $cwd, string $version, array $statuses): void
    {
        $filename = $this->getFilename($cwd);
        $manifest = $this->getExistingManifest($filename);
        $now = \date('Y-m-d H:i:s');

        foreach ($statuses as $status) {
            $statusString = $status->getStatus();
            $file = $this->removeCwd($cwd, $status->getFile()->getFilename());

            if (\in_array($statusString, FileGeneratorInterface::STATUSES_TO_TRIGGER_MANIFEST, true)) {
                $manifest[$file] = [
                    'date' => $now,
                    'hash' => $status->getHash(),
                    'status' => $statusString,
                    'version' => $version
                ];
            }
        }

        $this->filesystem->dumpFile(
            $filename,
            (string)\json_encode($manifest, \JSON_PRETTY_PRINT | \JSON_UNESCAPED_SLASHES)
        );
    }

    /**
     * @return mixed[]
     */
    private function getExistingManifest(string $filename): array
    {
        if ($this->filesystem->exists($filename)) {
            return \json_decode((string)\file_get_contents($filename), true);
        }

        return [];
    }

    private function getFilename(string $cwd): string
    {
        return \sprintf('%s/%s', $cwd, self::MANIFEST_NAME);
    }

    private function removeCwd(string $cwd, string $filename): string
    {
        return \str_replace($cwd . '/', '', $filename);
    }
}
