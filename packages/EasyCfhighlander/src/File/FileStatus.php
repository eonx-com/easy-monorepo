<?php
declare(strict_types=1);

namespace EonX\EasyCfhighlander\File;

final class FileStatus
{
    /** @var \EonX\EasyCfhighlander\File\File */
    private $file;

    /** @var null|string */
    private $hash;

    /** @var string */
    private $status;

    /**
     * FileStatus constructor.
     *
     * @param \EonX\EasyCfhighlander\File\File $file
     * @param string $status
     * @param null|string $hash
     */
    public function __construct(File $file, string $status, ?string $hash = null)
    {
        $this->file = $file;
        $this->hash = $hash;
        $this->status = $status;
    }

    /**
     * Get file.
     *
     * @return \EonX\EasyCfhighlander\File\File
     */
    public function getFile(): File
    {
        return $this->file;
    }

    /**
     * Get hash.
     *
     * @return null|string
     */
    public function getHash(): ?string
    {
        return $this->hash;
    }

    /**
     * Get status.
     *
     * @return string
     */
    public function getStatus(): string
    {
        return $this->status;
    }
}
