<?php
declare(strict_types=1);

namespace EonX\EasyCfhighlander\File;

use EonX\EasyCfhighlander\Interfaces\FileGeneratorInterface;
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
     * @param \EonX\EasyCfhighlander\File\File $fileToGenerate
     * @param null|mixed[] $params
     *
     * @return \EonX\EasyCfhighlander\File\FileStatus
     *
     * @throws \Twig\Error\LoaderError
     * @throws \Twig\Error\RuntimeError
     * @throws \Twig\Error\SyntaxError
     */
    public function generate(File $fileToGenerate, ?array $params = null): FileStatus
    {
        $filename = $fileToGenerate->getFilename();
        $rendered = $this->renderTemplate($fileToGenerate->getTemplate(), $params);
        $renderedHash = $this->hash($rendered);
        $exists = $this->filesystem->exists($filename);
        $identical = $exists ? $renderedHash === $this->hash((string)\file_get_contents($filename)) : null;

        // If file already exists but is not identical
        if ($identical === false) {
            $filename .= '_new';
        }

        // If file already exists and is identical, skip
        if ($identical) {
            return new FileStatus(
                $fileToGenerate,
                self::STATUS_SKIPPED_IDENTICAL,
                $this->hash((string)\file_get_contents($filename))
            );
        }

        $this->filesystem->dumpFile($filename, $rendered);
        $this->filesystem->chmod($filename, 0755);

        return new FileStatus($fileToGenerate, $exists ? self::STATUS_UPDATED : self::STATUS_CREATED, $renderedHash);
    }

    /**
     * Remove given file.
     *
     * @param \EonX\EasyCfhighlander\File\File $fileToRemove
     *
     * @return \EonX\EasyCfhighlander\File\FileStatus
     */
    public function remove(File $fileToRemove): FileStatus
    {
        if ($this->filesystem->exists($fileToRemove->getFilename()) === false) {
            return new FileStatus($fileToRemove, self::STATUS_SKIPPED_NO_FILE);
        }

        $this->filesystem->remove($fileToRemove->getFilename());

        return new FileStatus($fileToRemove, self::STATUS_REMOVED);
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
