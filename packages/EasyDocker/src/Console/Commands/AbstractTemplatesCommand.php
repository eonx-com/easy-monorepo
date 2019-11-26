<?php
declare(strict_types=1);

namespace EonX\EasyDocker\Console\Commands;

use EoneoPay\Utils\Interfaces\StrInterface;
use EonX\EasyDocker\File\File;
use EonX\EasyDocker\File\FileStatus;
use EonX\EasyDocker\Interfaces\FileGeneratorInterface;
use EonX\EasyDocker\Interfaces\ManifestGeneratorInterface;
use EonX\EasyDocker\Interfaces\ParameterResolverInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Filesystem\Filesystem;

abstract class AbstractTemplatesCommand extends Command
{
    /** @var string[] */
    private const CHECKS = [
        'elasticsearch_enabled' => 'elasticsearch'
    ];

    public const EXIT_CODE_ERROR = 1;

    public const EXIT_CODE_SUCCESS = 0;

    /** @var \EonX\EasyDocker\Interfaces\FileGeneratorInterface */
    private $fileGenerator;

    /** @var \Symfony\Component\Filesystem\Filesystem */
    private $filesystem;

    /** @var \EonX\EasyDocker\Interfaces\ManifestGeneratorInterface */
    private $manifestGenerator;

    /** @var \EonX\EasyDocker\Interfaces\ParameterResolverInterface */
    private $parameterResolver;

    /** @var \EoneoPay\Utils\Interfaces\StrInterface */
    private $str;

    /**
     * AbstractTemplatesCommand constructor.
     *
     * @param \EonX\EasyDocker\Interfaces\FileGeneratorInterface $fileGenerator
     * @param \Symfony\Component\Filesystem\Filesystem $filesystem
     * @param \EonX\EasyDocker\Interfaces\ManifestGeneratorInterface $manifestGenerator
     * @param \EonX\EasyDocker\Interfaces\ParameterResolverInterface $parameterResolver
     * @param \EoneoPay\Utils\Interfaces\StrInterface $str
     */
    public function __construct(
        FileGeneratorInterface $fileGenerator,
        Filesystem $filesystem,
        ManifestGeneratorInterface $manifestGenerator,
        ParameterResolverInterface $parameterResolver,
        StrInterface $str
    ) {
        parent::__construct();

        $this->fileGenerator = $fileGenerator;
        $this->filesystem = $filesystem;
        $this->manifestGenerator = $manifestGenerator;
        $this->parameterResolver = $parameterResolver;
        $this->str = $str;
    }

    /**
     * Get simple files names. Simple files are the one which don't require the filename to change.
     *
     * @return string[]
     */
    abstract protected function getSimpleFiles(): array;

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
        $cwd = $input->getOption('cwd') ?? \getcwd();
        $easyDirectory = $this->getEasyDirectory($cwd);

        foreach ($this->getParamResolvers($style) as $param => $resolver) {
            $this->parameterResolver->addResolver($param, $resolver);
        }

        $params = $this->parameterResolver
            ->setCachePathname(\sprintf('%s/easy-docker-params.yaml', $easyDirectory))
            ->resolve($input);

        $files = [];
        foreach ($this->getSimpleFiles() as $file) {
            $files[] = $this->getSimpleFile($cwd, $file);
        }

        ProgressBar::setFormatDefinition('custom', ' %current%/%max% [%bar%] %percent:3s%% %message%');
        $progress = new ProgressBar($style, \count($files));
        $progress->setFormat('custom');
        $progress->setOverwrite(false);

        if ($this->filesystem->exists($cwd) === false) {
            $this->filesystem->mkdir($cwd);
        }

        // Create easy directory if required
        if ($this->filesystem->exists($easyDirectory) === false) {
            $this->filesystem->mkdir($easyDirectory);
        }

        $style->write(\sprintf("Generating files in <comment>%s</comment>:\n", \realpath($cwd)));

        $statuses = [];

        foreach ($files as $file) {
            /** @var \EonX\EasyDocker\File\File $file */
            $statuses[] = $status = $this->processFile($file, $params);

            $progress->setMessage(\sprintf(
                '- <comment>%s</comment> <info>%s</info>...',
                $status->getStatus(),
                $file->getFilename()
            ));
            $progress->advance();
        }

        $this->manifestGenerator->generate($easyDirectory, $this->getApplication()->getVersion(), $statuses);

        return self::EXIT_CODE_SUCCESS;
    }

    /**
     * Get file for given name.
     *
     * @param string $cwd
     * @param string $name
     *
     * @return \EonX\EasyDocker\File\File
     */
    protected function getSimpleFile(string $cwd, string $name): File
    {
        return new File(\sprintf('%s/%s', $cwd, $name), $this->getTemplateName($name));
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
     * Get boolean param as string.
     *
     * @param null|mixed $param
     *
     * @return string
     */
    private function getBooleanParamAsString($param = null): string
    {
        return ((bool)$param) ? 'true' : 'false';
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
     * Determine if the .easy directory exists
     *
     * @param string $cwd
     *
     * @return string
     */
    private function getEasyDirectory(string $cwd): string
    {
        // If easy-docker* file already exists in cwd, .easy directory will not be used/created
        if (\file_exists($cwd . \DIRECTORY_SEPARATOR . 'easy-docker-params.yaml') === true) {
            return $cwd;
        }

        // Otherwise return path to .easy directory
        return \implode(\DIRECTORY_SEPARATOR, [$cwd, '.easy']);
    }

    /**
     * Get parameter resolvers.
     *
     * @param \Symfony\Component\Console\Style\SymfonyStyle $style
     *
     * @return iterable<string, callable>
     */
    private function getParamResolvers(SymfonyStyle $style): iterable
    {
        $required = $this->getRequiredParamValidator();
        $boolean = $this->getBooleanParamValidator();

        // Project Name
        yield 'project' => function (array $params) use ($style, $required): string {
            return $style->ask('Project Name', $params['project'] ?? null, $required);
        };

        // Elasticsearch enabled
        yield 'elasticsearch_enabled' => function (array $params) use ($style, $boolean): bool {
            return $style->ask(
                'Elasticsearch enabled',
                $this->getBooleanParamAsString($params['elasticsearch_enabled'] ?? null),
                $boolean
            );
        };

        // SOAP
        yield 'soap' => function (array $params) use ($style, $boolean): bool {
            return $style->ask(
                'Install PHP-Soap',
                $this->getBooleanParamAsString($params['soap'] ?? null),
                $boolean
            );
        };

        // Doctrine Migrations
        yield 'doctrine_migrations_enabled' => function (array $params) use ($style, $boolean): bool {
            return $style->ask(
                'Is DoctrineMigrations enabled?',
                $this->getBooleanParamAsString($params['doctrine_migrations_enabled'] ?? null),
                $boolean
            );
        };

        // Newrelic
        yield 'prestissimo' => function (array $params) use ($style, $boolean): bool {
            return $style->ask(
                'Install prestissimo plugin for composer?',
                $this->getBooleanParamAsString($params['prestissimo'] ?? null),
                $boolean
            );
        };
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

    /**
     * Process file.
     *
     * @param \EonX\EasyDocker\File\File $file
     * @param mixed[] $params
     *
     * @return \EonX\EasyDocker\File\FileStatus
     */
    private function processFile(File $file, array $params): FileStatus
    {
        foreach (self::CHECKS as $param => $path) {
            $check = (bool)($params[$param] ?? false);

            if ($check === false && $this->str->contains($file->getFilename(), $path)) {
                return $this->fileGenerator->remove($file);
            }
        }

        return $this->fileGenerator->generate($file, $params);
    }
}
