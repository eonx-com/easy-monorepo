<?php
declare(strict_types=1);

namespace EonX\EasyTest\Coverage\Command;

use EonX\EasyTest\Coverage\Loader\CoverageLoaderInterface;
use EonX\EasyTest\Coverage\Locator\CoverageResolverLocatorInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'easy-test:check-coverage',
    description: 'Runs given test script and checks output coverage against coverage option'
)]
final class CheckCoverageCommand extends Command
{
    public function __construct(
        private CoverageLoaderInterface $coverageLoader,
        private CoverageResolverLocatorInterface $coverageResolverLocator,
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
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
