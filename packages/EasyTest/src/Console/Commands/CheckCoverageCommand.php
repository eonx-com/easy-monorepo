<?php

declare(strict_types=1);

namespace EonX\EasyTest\Console\Commands;

use EonX\EasyTest\Interfaces\CoverageLoaderInterface;
use EonX\EasyTest\Interfaces\CoverageResolverLocatorInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

final class CheckCoverageCommand extends Command
{
    /**
     * @var string
     */
    protected static $defaultName = 'check-coverage';

    /**
     * @var \EonX\EasyTest\Interfaces\CoverageLoaderInterface
     */
    private $coverageLoader;

    /**
     * @var \EonX\EasyTest\Interfaces\CoverageResolverLocatorInterface
     */
    private $coverageResolverLocator;

    public function __construct(
        CoverageLoaderInterface $coverageLoader,
        CoverageResolverLocatorInterface $coverageResolverLocator
    ) {
        $this->coverageLoader = $coverageLoader;
        $this->coverageResolverLocator = $coverageResolverLocator;

        parent::__construct(null);
    }

    protected function configure(): void
    {
        $this
            ->setDescription('Run given test script and check output coverage against coverage option')
            ->addArgument('file', InputArgument::REQUIRED, 'File containing the coverage output')
            ->addOption('coverage', 'c', InputOption::VALUE_REQUIRED, 'Coverage limit to check against');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $style = new SymfonyStyle($input, $output);
        /** @var string $coverageValue */
        $coverageValue = $input->getOption('coverage');
        $checkCoverage = (float)$coverageValue;

        /** @var string $fileArgumentValue */
        $fileArgumentValue = $input->getArgument('file');
        $coverageResolver = $this->coverageResolverLocator->getCoverageResolver($fileArgumentValue);
        $coverageReport = $coverageResolver->resolve($this->coverageLoader->load($fileArgumentValue));

        if ($checkCoverage > $coverageReport->getCoverage()) {
            $style->error(
                \sprintf(
                    'Coverage "%d%%" is lower than expectation "%d%%"',
                    $coverageReport->getCoverage(),
                    $checkCoverage
                )
            );

            if ($coverageReport->hasViolations()) {
                $style->error(
                    \sprintf('Violations: %s', \implode(\PHP_EOL, $coverageReport->getViolations()))
                );
            }

            return 1;
        }

        $style->success(
            \sprintf('Yeah nah yeah nah yeah!! Good coverage mate! "%d%%"', $coverageReport->getCoverage())
        );

        return 0;
    }
}
