<?php
declare(strict_types=1);

namespace EonX\EasyDocker\File;

final class File
{
    /** @var string */
    private $filename;

    /** @var string */
    private $template;

    /**
     * FileToGenerate constructor.
     *
     * @param string $filename
     * @param string $template
     */
    public function __construct(string $filename, string $template)
    {
        $this->filename = $filename;
        $this->template = $template;
    }

    /**
     * Get file.
     *
     * @return string
     */
    public function getFilename(): string
    {
        return $this->filename;
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
