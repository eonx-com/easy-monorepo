<?php
declare(strict_types=1);

namespace LoyaltyCorp\EasyDocker\Console\Commands;

use LoyaltyCorp\EasyDocker\File\FileToGenerate;
use LoyaltyCorp\EasyDocker\Interfaces\FileGeneratorInterface;
use LoyaltyCorp\EasyDocker\Interfaces\ManifestGeneratorInterface;
use LoyaltyCorp\EasyDocker\Interfaces\ParameterResolverInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Filesystem\Filesystem;

abstract class AbstractTemplatesCommand extends Command
{
    public const EXIT_CODE_ERROR = 1;
    public const EXIT_CODE_SUCCESS = 0;

    /** @var \LoyaltyCorp\EasyDocker\Interfaces\FileGeneratorInterface */
    private $fileGenerator;

    /** @var \Symfony\Component\Filesystem\Filesystem */
    private $filesystem;

    /** @var \LoyaltyCorp\EasyDocker\Interfaces\ManifestGeneratorInterface */
    private $manifestGenerator;

    /** @var \LoyaltyCorp\EasyDocker\Interfaces\ParameterResolverInterface */
    private $parameterResolver;

    /**
     * AbstractTemplatesCommand constructor.
     *
     * @param \LoyaltyCorp\EasyDocker\Interfaces\FileGeneratorInterface $fileGenerator
     * @param \Symfony\Component\Filesystem\Filesystem $filesystem
     * @param \LoyaltyCorp\EasyDocker\Interfaces\ManifestGeneratorInterface $manifestGenerator
     * @param \LoyaltyCorp\EasyDocker\Interfaces\ParameterResolverInterface $parameterResolver
     */
    public function __construct(
        FileGeneratorInterface $fileGenerator,
        Filesystem $filesystem,
        ManifestGeneratorInterface $manifestGenerator,
        ParameterResolverInterface $parameterResolver
    ) {
        parent::__construct();

        $this->fileGenerator = $fileGenerator;
        $this->filesystem = $filesystem;
        $this->manifestGenerator = $manifestGenerator;
        $this->parameterResolver = $parameterResolver;
    }

    /**
     * Get simple files names. Simple files are the one which don't require the filename to change.
     *
     * @return string[]
     */
    abstract protected function getSimpleFiles(): array;

    /**
     * Add parameters resolver.
     *
     * @param \Symfony\Component\Console\Style\SymfonyStyle $style
     *
     * @return void
     */
    protected function addParamResolver(SymfonyStyle $style): void
    {
        $this->parameterResolver->addResolver(function (array $params) use ($style): array {
            $required = $this->getRequiredParamValidator();
            $boolean = $this->getBooleanParamValidator();

            return [
                'project' => $style->ask('Project name', $params['project'] ?? null, $required),
                'newrelic' => $style->ask(
                    'Install New Relic',
                    (bool)($params['newrelic'] ?? null) ? 'true' : 'false',
                    $boolean
                ),
                'soap' => $style->ask(
                    'Install PHP-Soap',
                    (bool)($params['soap'] ?? null) ? 'true' : 'false',
                    $boolean
                ),
            ];
        });
    }

    /**
     * Configure command.
     *
     * @return void
     */
    protected function configure(): void
    {
        $this->addOption('cwd', null, InputOption::VALUE_OPTIONAL, 'Current working directory', \getcwd());
    }

    /**
     * Execute command.
     *
     * @param \Symfony\Component\Console\Input\InputInterface $input
     * @param \Symfony\Component\Console\Output\OutputInterface $output
     *
     * @return null|int
     */
    protected function execute(InputInterface $input, OutputInterface $output): ?int
    {
        $style = new SymfonyStyle($input, $output);
        $this->addParamResolver($style);

        $cwd = $input->getOption('cwd') ?? \getcwd();

        $params = $this->parameterResolver
            ->setCachePathname(\sprintf('%s/easy-docker-params.yaml', $cwd))
            ->resolve($input);

        $files = [];
        foreach ($this->getSimpleFiles() as $file) {
            $files[] = $this->getSimpleFileToGenerate($cwd, $file);
        }

        ProgressBar::setFormatDefinition('custom', ' %current%/%max% [%bar%] %percent:3s%% %message%');
        $progress = new ProgressBar($style, \count($files));
        $progress->setFormat('custom');
        $progress->setOverwrite(false);

        if ($this->filesystem->exists($cwd) === false) {
            $this->filesystem->mkdir($cwd);
        }

        $style->write(\sprintf("Generating files in <comment>%s</comment>:\n", \realpath($cwd)));

        $statuses = [];

        foreach ($files as $file) {
            /** @var \LoyaltyCorp\EasyDocker\File\FileToGenerate $file */
            $statuses[] = $status = $this->fileGenerator->generate($file, $params);

            $progress->setMessage(\sprintf(
                '- <comment>%s</comment> <info>%s</info>...',
                $status->getStatus(),
                $file->getFilename()
            ));
            $progress->advance();
        }

        $this->manifestGenerator->generate($cwd, $this->getApplication()->getVersion(), $statuses);

        return self::EXIT_CODE_SUCCESS;
    }

    /**
     * Get validator for boolean parameters.
     *
     * @return \Closure
     */
    private function getBooleanParamValidator(): \Closure
    {
        return static function ($answer): bool {
            if (empty($answer)) {
                return false;
            }

            $answer = \strtolower((string)$answer);

            if (\in_array($answer, ['true', 'false'], true) === false) {
                throw new \RuntimeException('The value must be either empty, or a string "true" or "false"');
            }

            return $answer === 'true';
        };
    }

    /**
     * Get file to generate for given name.
     *
     * @param string $cwd
     * @param string $name
     *
     * @return \LoyaltyCorp\EasyDocker\File\FileToGenerate
     */
    protected function getSimpleFileToGenerate(string $cwd, string $name): FileToGenerate
    {
        return new FileToGenerate(\sprintf('%s/%s', $cwd, $name), $this->getTemplateName($name));
    }

    /**
     * Get template name for given template.
     *
     * @param string $template
     *
     * @return string
     */
    protected function getTemplateName(string $template): string
    {
        return \sprintf('%s.twig', $template);
    }

    /**
     * Get validator for required parameters.
     *
     * @return \Closure
     */
    private function getRequiredParamValidator(): \Closure
    {
        return static function ($answer): string {
            if (empty($answer)) {
                throw new \RuntimeException('A value is required');
            }

            return \str_replace(' ', '', (string)$answer);
        };
    }
}
