<?php
declare(strict_types=1);

namespace LoyaltyCorp\EasyCfhighlander\File;

final class FileToGenerate
{
    /** @var string */
    private $file;

    /** @var string */
    private $template;

    /**
     * FileToGenerate constructor.
     *
     * @param string $file
     * @param string $template
     */
    public function __construct(string $file, string $template)
    {
        $this->file = $file;
        $this->template = $template;
    }

    /**
     * Get file.
     *
     * @return string
     */
    public function getFile(): string
    {
        return $this->file;
    }

    /**
     * Get template.
     *
     * @return string
     */
    public function getTemplate(): string
    {
        return $this->template;
    }
}
