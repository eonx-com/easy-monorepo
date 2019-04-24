<?php
declare(strict_types=1);

namespace LoyaltyCorp\EasyCfhighlander\File;

use LoyaltyCorp\EasyCfhighlander\Interfaces\FileGeneratorInterface;
use Symfony\Component\Filesystem\Filesystem;
use Twig\Environment;

final class FileGenerator implements FileGeneratorInterface
{
    /** @var \Symfony\Component\Filesystem\Filesystem */
    private $filesystem;

    /** @var \Twig\Environment */
    private $twig;

    /**
     * FileGenerator constructor.
     *
     * @param \Twig\Environment $twig
     */
    public function __construct(Environment $twig)
    {
        $this->filesystem = new Filesystem();
        $this->twig = $twig;
    }

    /**
     * Generate file for given template and params.
     *
     * @param string $filename
     * @param string $template
     * @param null|mixed[] $params
     *
     * @return void
     *
     * @throws \Twig\Error\LoaderError
     * @throws \Twig\Error\RuntimeError
     * @throws \Twig\Error\SyntaxError
     */
    public function generate(string $filename, string $template, ?array $params = null): void
    {
        $this->filesystem->dumpFile($filename, $this->twig->render($template, $params ?? []));
    }
}
