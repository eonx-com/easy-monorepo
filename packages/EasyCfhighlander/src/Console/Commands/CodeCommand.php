<?php
declare(strict_types=1);

namespace LoyaltyCorp\EasyCfhighlander\Console\Commands;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symplify\PackageBuilder\Console\Command\CommandNaming;

final class CodeCommand extends AbstractTemplatesCommand
{
    /**
     * Configure command.
     *
     * @return void
     */
    protected function configure(): void
    {
        $this->setName('code');
        $this->setDescription('Generates Cfhighlander templates for Code repository.');

        parent::configure();
    }

    /**
     * Get project files names.
     *
     * @return string[]
     */
    protected function getProjectFiles(): array
    {
        return [
            'project.cfhighlander.rb',
            'project.config.yaml',
            'project-schema.cfhighlander.rb',
            'project-schema.config.yaml'
        ];
    }

    /**
     * Get simple files names.
     *
     * @return string[]
     */
    protected function getSimpleFiles(): array
    {
        return [
            'Jenkinsfile.twig'
        ];
    }

    /**
     * Get template prefix.
     *
     * @return string
     */
    protected function getTemplatePrefix(): string
    {
        return 'code';
    }
}
