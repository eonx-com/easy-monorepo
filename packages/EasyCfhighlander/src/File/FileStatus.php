<?php
declare(strict_types=1);

namespace LoyaltyCorp\EasyCfhighlander\File;

final class FileStatus
{
    /** @var \LoyaltyCorp\EasyCfhighlander\File\FileToGenerate */
    private $file;

    /** @var string */
    private $hash;

    /** @var string */
    private $status;

    /**
     * FileStatus constructor.
     *
     * @param \LoyaltyCorp\EasyCfhighlander\File\FileToGenerate $file
     * @param string $hash
     * @param string $status
     */
    public function __construct(FileToGenerate $file, string $hash, string $status)
    {
        $this->file = $file;
        $this->hash = $hash;
        $this->status = $status;
    }

    /**
     * Get file.
     *
     * @return \LoyaltyCorp\EasyCfhighlander\File\FileToGenerate
     */
    public function getFile(): FileToGenerate
    {
        return $this->file;
    }

    /**
     * Get hash.
     *
     * @return string
     */
    public function getHash(): string
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
