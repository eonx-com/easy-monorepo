<?php
declare(strict_types=1);

namespace EonX\EasyCfhighlander\File;

final class FileStatus
{
    /**
     * @var \EonX\EasyCfhighlander\File\File
     */
    private $file;

    /**
     * @var null|string
     */
    private $hash;

    /**
     * @var string
     */
    private $status;

    public function __construct(File $file, string $status, ?string $hash = null)
    {
        $this->file = $file;
        $this->hash = $hash;
        $this->status = $status;
    }

    public function getFile(): File
    {
        return $this->file;
    }

    public function getHash(): ?string
    {
        return $this->hash;
    }

    public function getStatus(): string
    {
        return $this->status;
    }
}
