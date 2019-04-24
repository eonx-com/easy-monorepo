<?php
declare(strict_types=1);

namespace LoyaltyCorp\EasyCfhighlander\Console\Commands;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symplify\PackageBuilder\Console\Command\CommandNaming;

final class CloudFormationCommand extends AbstractTemplatesCommand
{
    /** @var string[] */
    private static $projectFiles = [
        'cfhighlander.rb',
        'config.yaml',
        'mappings.yaml'
    ];

    /** @var string[] */
    private static $simpleFiles = [
        'Jenkinsfile',
        'aurora.config.yaml',
        'az.mappings.yaml',
        'bastion.config.yaml',
        'loadbalancer.config.yaml',
        'redis.config.yaml',
        'sqs.config.yaml',
        'vpc.config.yaml'
    ];

    /**
     * Configure command.
     *
     * @return void
     */
    protected function configure(): void
    {
        $this->setName(CommandNaming::classToName(self::class));
        $this->setDescription('Generates Cfhighlander templates for CloudFormation repository.');

        parent::configure();
    }

    /**
     * Do get files to generator from children commands.
     *
     * @param \Symfony\Component\Console\Input\InputInterface $input
     * @param \Symfony\Component\Console\Output\OutputInterface $output
     * @param mixed[] $params
     *
     * @return mixed[]
     */
    protected function doGetFilesToGenerate(InputInterface $input, OutputInterface $output, array $params): array
    {
        $project = $params['project'];
        $files = [];

        foreach (static::$projectFiles as $file) {
            $files[] = $this->getProjectFileToGenerate($file, $project);
        }

        foreach (static::$simpleFiles as $file) {
            $files[] = $this->getSimpleFileToGenerate($file);
        }

        return $files;
    }

    /**
     * Get template prefix.
     *
     * @return string
     */
    protected function getTemplatePrefix(): string
    {
        return 'cloudformation';
    }
}
