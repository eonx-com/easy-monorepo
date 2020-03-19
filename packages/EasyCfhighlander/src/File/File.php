<?php

declare(strict_types=1);

namespace EonX\EasyCfhighlander\File;

final class File
{
    /**
     * @var string
     */
    private $filename;

    /**
     * @var string
     */
    private $template;

    public function __construct(string $filename, string $template)
    {
        $this->filename = $filename;
        $this->template = $template;
    }

    public function getFilename(): string
    {
        return $this->filename;
    }

    public function getTemplate(): string
    {
        return $this->template;
    }
}
