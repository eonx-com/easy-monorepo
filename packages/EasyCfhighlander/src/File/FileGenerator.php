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
     * @param \Symfony\Component\Filesystem\Filesystem $filesystem
     */
    public function __construct(Environment $twig, Filesystem $filesystem)
    {
        $this->filesystem = $filesystem;
        $this->twig = $twig;
    }

    /**
     * Generate file for given template and params.
     *
     * @param \LoyaltyCorp\EasyCfhighlander\File\FileToGenerate $fileToGenerate
     * @param null|mixed[] $params
     *
     * @return \LoyaltyCorp\EasyCfhighlander\File\FileStatus
     *
     * @throws \Twig\Error\LoaderError
     * @throws \Twig\Error\RuntimeError
     * @throws \Twig\Error\SyntaxError
     */
    public function generate(FileToGenerate $fileToGenerate, ?array $params = null): FileStatus
    {
        $filename = $fileToGenerate->getFilename();
        $rendered = $this->renderTemplate($fileToGenerate->getTemplate(), $params);
        $renderedHash = $this->hash($rendered);
        $exists = $this->filesystem->exists($filename);

        // If file already exist and is identical, skip
        if ($exists && $renderedHash === $this->hash(\file_get_contents($filename))) {
            return new FileStatus(
                $fileToGenerate,
                $this->hash(\file_get_contents($filename)),
                self::STATUS_SKIPPED_IDENTICAL
            );
        }

        $this->filesystem->dumpFile($filename, $rendered);

        return new FileStatus($fileToGenerate, $renderedHash, $exists ? self::STATUS_UPDATED : self::STATUS_CREATED);
    }

    /**
     * Hash given content.
     *
     * @param string $content
     *
     * @return string
     */
    private function hash(string $content): string
    {
        return \md5($content);
    }

    /**
     * Return given template for given params.
     *
     * @param string $template
     * @param null|array $params
     *
     * @return string
     *
     * @throws \Twig\Error\LoaderError
     * @throws \Twig\Error\RuntimeError
     * @throws \Twig\Error\SyntaxError
     */
    private function renderTemplate(string $template, ?array $params = null): string
    {
        return $this->twig->render($template, $params ?? []);
    }
}
