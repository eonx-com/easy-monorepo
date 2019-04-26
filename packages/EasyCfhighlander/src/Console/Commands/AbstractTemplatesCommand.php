<?php
declare(strict_types=1);

namespace LoyaltyCorp\EasyCfhighlander\Console\Commands;

use LoyaltyCorp\EasyCfhighlander\File\FileToGenerate;
use LoyaltyCorp\EasyCfhighlander\Interfaces\FileGeneratorInterface;
use LoyaltyCorp\EasyCfhighlander\Interfaces\ParameterResolverInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

abstract class AbstractTemplatesCommand extends Command
{
    public const EXIT_CODE_ERROR = 1;
    public const EXIT_CODE_SUCCESS = 0;

    /** @var \LoyaltyCorp\EasyCfhighlander\Interfaces\FileGeneratorInterface */
    private $fileGenerator;

    /** @var \LoyaltyCorp\EasyCfhighlander\Interfaces\ParameterResolverInterface */
    private $parameterResolver;

    /**
     * AbstractTemplatesCommand constructor.
     *
     * @param \LoyaltyCorp\EasyCfhighlander\Interfaces\FileGeneratorInterface $fileGenerator
     * @param \LoyaltyCorp\EasyCfhighlander\Interfaces\ParameterResolverInterface $parameterResolver
     */
    public function __construct(FileGeneratorInterface $fileGenerator, ParameterResolverInterface $parameterResolver)
    {
        parent::__construct();

        $this->fileGenerator = $fileGenerator;
        $this->parameterResolver = $parameterResolver;
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
            $validator = static function ($answer): string {
                if (empty($answer)) {
                    throw new \RuntimeException('A value is required');
                }

                return \str_replace(' ', '', \strtolower($answer));
            };

            return [
                'project' => $style->ask('Project name', $params['project'] ?? null, $validator),
                'dns_domain' => $style->ask('DNS domain', $params['dns_domain'] ?? null, $validator),
                'dev_account' => $style->ask('AWS DEV account', $params['dev_account'] ?? null, $validator),
                'ops_account' => $style->ask('AWS OPS account', $params['ops_account'] ?? null, $validator),
                'prod_account' => $style->ask('AWS PROD account', $params['prod_account'] ?? null, $validator)
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
        $this->addParamResolver(new SymfonyStyle($input, $output));

        $params = $this->parameterResolver->resolve($input);
        $files = [];

        foreach ($this->getProjectFiles() as $file) {
            $files[] = $this->getProjectFileToGenerate($file, $params['project']);
        }

        foreach ($this->getSimpleFiles() as $file) {
            $files[] = $this->getSimpleFileToGenerate($file);
        }

        /** @var \Symfony\Component\Console\Output\ConsoleOutput $output */
        $progressSection = new SymfonyStyle($input, $output->section());
        $reportSection = new SymfonyStyle($input, $output->section());

        $progressSection->title('Progress');
        $reportSection->title('Report');

        $progress = new ProgressBar($progressSection, \count($files));
        $progress->start();

        $cwd = $input->getOption('cwd') ?? \getcwd();

        $reportSection->writeln(\sprintf("Generating files in <comment>%s</comment>:\n", \realpath($cwd)));

        foreach ($files as $file) {
            $this->fileGenerator->generate($cwd . '/' . $file->getFile(), $file->getTemplate(), $params);

            $progress->advance();
            $reportSection->write(\sprintf('  - Generating <info>%s</info>...', $file->getFile()));
        }

        return self::EXIT_CODE_SUCCESS;
    }

    /**
     * Get project file to generate.
     *
     * @param string $name
     * @param string $project
     *
     * @return \LoyaltyCorp\EasyCfhighlander\File\FileToGenerate
     */
    protected function getProjectFileToGenerate(string $name, string $project): FileToGenerate
    {
        return new FileToGenerate(\str_replace('project', $project, $name), $this->getTemplateName($name));
    }

    /**
     * Get file to generate for given name.
     *
     * @param string $name
     *
     * @return \LoyaltyCorp\EasyCfhighlander\File\FileToGenerate
     */
    protected function getSimpleFileToGenerate(string $name): FileToGenerate
    {
        return new FileToGenerate($name, $this->getTemplateName($name));
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
}
