<?php
declare(strict_types=1);

namespace LoyaltyCorp\EasyCfhighlander\Console\Commands;

use EoneoPay\Utils\Interfaces\StrInterface;
use LoyaltyCorp\EasyCfhighlander\File\File;
use LoyaltyCorp\EasyCfhighlander\File\FileStatus;
use LoyaltyCorp\EasyCfhighlander\Interfaces\FileGeneratorInterface;
use LoyaltyCorp\EasyCfhighlander\Interfaces\ManifestGeneratorInterface;
use LoyaltyCorp\EasyCfhighlander\Interfaces\ParameterResolverInterface;
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

    private const CHECKS = [
        'elasticsearch_enabled' => 'elasticsearch',
        'redis_enabled' => 'redis'
    ];

    /** @var \LoyaltyCorp\EasyCfhighlander\Interfaces\FileGeneratorInterface */
    private $fileGenerator;

    /** @var \Symfony\Component\Filesystem\Filesystem */
    private $filesystem;

    /** @var \LoyaltyCorp\EasyCfhighlander\Interfaces\ManifestGeneratorInterface */
    private $manifestGenerator;

    /** @var \LoyaltyCorp\EasyCfhighlander\Interfaces\ParameterResolverInterface */
    private $parameterResolver;

    /** @var \EoneoPay\Utils\Interfaces\StrInterface */
    private $str;

    /**
     * AbstractTemplatesCommand constructor.
     *
     * @param \LoyaltyCorp\EasyCfhighlander\Interfaces\FileGeneratorInterface $fileGenerator
     * @param \Symfony\Component\Filesystem\Filesystem $filesystem
     * @param \LoyaltyCorp\EasyCfhighlander\Interfaces\ManifestGeneratorInterface $manifestGenerator
     * @param \LoyaltyCorp\EasyCfhighlander\Interfaces\ParameterResolverInterface $parameterResolver
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
     * Get project files names.
     *
     * @return string[]
     */
    abstract protected function getProjectFiles(): array;

    /**
     * Get simple files names.
     *
     * @return string[]
     */
    abstract protected function getSimpleFiles(): array;

    /**
     * Get template prefix.
     *
     * @return string
     */
    abstract protected function getTemplatePrefix(): string;

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
            $alpha = $this->getAlphaParamValidator();
            $boolean = $this->getBooleanParamValidator();
            $required = $this->getRequiredParamValidator();

            return [
                'project' => $style->ask('Project name', $params['project'] ?? null, $required),
                'db_name' => $style->ask('Database name', $params['db_name'] ?? null, $alpha),
                'db_username' => $style->ask('Database username', $params['db_username'] ?? null, $alpha),
                'dns_domain' => $style->ask('DNS domain', $params['dns_domain'] ?? null, $required),
                'redis_enabled' => $style->ask(
                    'Redis enabled',
                    $this->getBooleanParamAsString($params['redis_enabled'] ?? null),
                    $boolean
                ),
                'elasticsearch_enabled' => $style->ask(
                    'Elasticsearch enabled',
                    $this->getBooleanParamAsString($params['elasticsearch_enabled'] ?? null),
                    $boolean
                ),
                'ssm_prefix' => $style->ask('SSM Prefix', $params['ssm_prefix'] ?? null, $alpha),
                'dev_account' => $style->ask('AWS DEV account', $params['dev_account'] ?? null, $required),
                'ops_account' => $style->ask('AWS OPS account', $params['ops_account'] ?? null, $required),
                'prod_account' => $style->ask('AWS PROD account', $params['prod_account'] ?? null, $required)
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
            ->setCachePathname(\sprintf('%s/easy-cfhighlander-params.yaml', $cwd))
            ->resolve($input);

        $files = [];
        foreach ($this->getProjectFiles() as $file) {
            $files[] = $this->getProjectFileToGenerate($cwd, $file, $params['project']);
        }

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
            /** @var \LoyaltyCorp\EasyCfhighlander\File\File $file */
            $statuses[] = $status = $this->processFile($file, $params);

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
     * Get project file to generate.
     *
     * @param string $cwd
     * @param string $name
     * @param string $project
     *
     * @return \LoyaltyCorp\EasyCfhighlander\File\File
     */
    protected function getProjectFileToGenerate(string $cwd, string $name, string $project): File
    {
        $filename = \sprintf('%s/%s', $cwd, $name);

        return new File(\str_replace('project', $project, $filename), $this->getTemplateName($name));
    }

    /**
     * Get file to generate for given name.
     *
     * @param string $cwd
     * @param string $name
     *
     * @return \LoyaltyCorp\EasyCfhighlander\File\File
     */
    protected function getSimpleFileToGenerate(string $cwd, string $name): File
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
        return \sprintf('%s/%s.twig', $this->getTemplatePrefix(), $template);
    }

    /**
     * Get validator for required alphabetic parameters.
     *
     * @return \Closure
     */
    private function getAlphaParamValidator(): \Closure
    {
        return static function ($answer): string {
            if (empty($answer)) {
                throw new \RuntimeException('A value is required');
            }

            if (\ctype_alpha($answer) === false) {
                throw new \RuntimeException('Value must be strictly alphabetic');
            }

            return \str_replace(' ', '', (string)$answer);
        };
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
        return ((bool)($param)) ? 'true' : 'false';
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
     * @param \LoyaltyCorp\EasyCfhighlander\File\File $file
     * @param mixed[] $params
     *
     * @return \LoyaltyCorp\EasyCfhighlander\File\FileStatus
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
