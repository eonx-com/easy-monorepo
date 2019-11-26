<?php
declare(strict_types=1);

namespace EonX\EasyDocker\Console\Commands;

use EoneoPay\Utils\Interfaces\StrInterface;
use EonX\EasyDocker\Interfaces\FileGeneratorInterface;
use EonX\EasyDocker\Interfaces\ManifestGeneratorInterface;
use EonX\EasyDocker\Interfaces\ParameterResolverInterface;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Finder\Finder;

final class DockerFilesGeneratorCommand extends AbstractTemplatesCommand
{
    /** @var string */
    private const TEMPLATES = __DIR__ . '/../../../templates';

    /** @var \Symfony\Component\Finder\Finder */
    private $finder;

    /**
     * DockerFilesGeneratorCommand constructor.
     *
     * @param \EonX\EasyDocker\Interfaces\FileGeneratorInterface $fileGenerator
     * @param \Symfony\Component\Filesystem\Filesystem $filesystem
     * @param \Symfony\Component\Finder\Finder $finder
     * @param \EonX\EasyDocker\Interfaces\ManifestGeneratorInterface $manifestGenerator
     * @param \EonX\EasyDocker\Interfaces\ParameterResolverInterface $parameterResolver
     * @param \EoneoPay\Utils\Interfaces\StrInterface $str
     */
    public function __construct(
        FileGeneratorInterface $fileGenerator,
        Filesystem $filesystem,
        Finder $finder,
        ManifestGeneratorInterface $manifestGenerator,
        ParameterResolverInterface $parameterResolver,
        StrInterface $str
    ) {
        $this->finder = $finder;

        parent::__construct($fileGenerator, $filesystem, $manifestGenerator, $parameterResolver, $str);
    }

    /**
     * Configure command.
     *
     * @return void
     */
    protected function configure(): void
    {
        $this->setName('generate');
        $this->setDescription('Generates Docker files.');

        parent::configure();
    }

    /**
     * Get simple files names. Simple files are the one which don't require the filename to change.
     *
     * @return string[]
     */
    protected function getSimpleFiles(): array
    {
        $files = [];

        foreach ($this->finder->files()->in(self::TEMPLATES) as $file) {
            /** @var \Symfony\Component\Finder\SplFileInfo $file */
            $files[] = \str_replace('.twig', '', $file->getRelativePathname());
        }

        return $files;
    }
}
