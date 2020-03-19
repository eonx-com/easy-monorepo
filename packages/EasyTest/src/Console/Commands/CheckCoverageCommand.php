<?php

declare(strict_types=1);

namespace EonX\EasyTest\Console\Commands;

use EonX\EasyTest\Interfaces\CoverageLoaderInterface;
use EonX\EasyTest\Interfaces\CoverageResolverInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

final class CheckCoverageCommand extends Command
{
    /**
     * @var \EonX\EasyTest\Interfaces\CoverageLoaderInterface
     */
    private $coverageLoader;

    /**
     * @var \EonX\EasyTest\Interfaces\CoverageResolverInterface
     */
    private $coverageResolver;

    public function __construct(CoverageLoaderInterface $coverageLoader, CoverageResolverInterface $coverageResolver)
    {
        $this->coverageLoader = $coverageLoader;
        $this->coverageResolver = $coverageResolver;

        parent::__construct(null);
    }

    protected function configure(): void
    {
        $this
            ->setName('check-coverage')
            ->setDescription('Run given test script and check output coverage against coverage option')
            ->addArgument(
                'file',
                InputArgument::REQUIRED,
                'File containing the coverage output'
            )
            ->addOption(
                'coverage',
                'c',
                InputOption::VALUE_REQUIRED,
                'Coverage limit to check against'
            );
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $style = new SymfonyStyle($input, $output);
        $checkCoverage = (float)$input->getOption('coverage');

        $coverage = $this->coverageResolver->resolve($this->coverageLoader->load((string)$input->getArgument('file')));

        if ($checkCoverage > $coverage) {
            $style->error(\sprintf('Coverage "%d%%" is lower than expectation "%d%%"', $coverage, $checkCoverage));

            return 1;
        }

        $style->success(\sprintf('Yeah nah yeah nah yeah!! Good coverage mate! "%d%%"', $coverage));

        return 0;
    }
}
