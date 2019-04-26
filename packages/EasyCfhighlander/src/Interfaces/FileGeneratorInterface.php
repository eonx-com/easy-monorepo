<?php
declare(strict_types=1);

namespace LoyaltyCorp\EasyCfhighlander\Interfaces;

interface FileGeneratorInterface
{
    /**
     * Generate file for given template and params.
     *
     * @param string $filename
     * @param string $template
     * @param null|mixed[] $params
     *
     * @return void
     */
    public function generate(string $filename, string $template, ?array $params = null): void;
}
