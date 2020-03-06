<?php
declare(strict_types=1);

namespace EonX\EasyDocker\Console\Commands;

use EonX\EasyDocker\Interfaces\FileGeneratorInterface;
use EonX\EasyDocker\Interfaces\ManifestGeneratorInterface;
use EonX\EasyDocker\Interfaces\ParameterResolverInterface;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Finder\Finder;

final class DockerFilesGeneratorCommand extends AbstractTemplatesCommand
{
    /**
     * @var string
     */
    private const TEMPLATES = __DIR__ . '/../../../templates';

    /**
     * @var \Symfony\Component\Finder\Finder
     */
    private $finder;

    public function __construct(
        FileGeneratorInterface $fileGenerator,
        Filesystem $filesystem,
        Finder $finder,
        ManifestGeneratorInterface $manifestGenerator,
        ParameterResolverInterface $parameterResolver
    ) {
        $this->finder = $finder;

        parent::__construct($fileGenerator, $filesystem, $manifestGenerator, $parameterResolver);
    }

    protected function configure(): void
    {
        $this->setName('generate');
        $this->setDescription('Generates Docker files.');

        parent::configure();
    }

    /**
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
