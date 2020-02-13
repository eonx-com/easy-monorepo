<?php
declare(strict_types=1);

namespace EonX\EasyTest\Console\Commands;

use Nette\Utils\Strings;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Process\Process;

final class CheckCoverageCommand extends Command
{
    /**
     * Configure command.
     *
     * @return void
     */
    protected function configure(): void
    {
        $this
            ->setName('check-coverage')
            ->setDescription('Run given test script and check output coverage against coverage option')
            ->addArgument(
                'script',
                InputArgument::REQUIRED,
                'Test script to run'
            )
            ->addOption(
                'coverage',
                'c',
                InputOption::VALUE_REQUIRED,
                'Coverage limit to check against'
            );
    }

    /**
     * Run given script and assert coverage.
     *
     * @param \Symfony\Component\Console\Input\InputInterface $input
     * @param \Symfony\Component\Console\Output\OutputInterface $output
     *
     * @return int
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $style = new SymfonyStyle($input, $output);
        $checkCoverage = (float)$input->getOption('coverage');
        $process = $this->runProcess($input, $output);

        if (($process->getExitCode() ?? 0) !== 0) {
            return $process->getExitCode();
        }

        $coverage = $this->getCoverage($process->getOutput());

        if ($coverage === null) {
            $style->error('No coverage found in output');

            return 1;
        }

        if ($checkCoverage > $coverage) {
            $style->error(\sprintf('Coverage "%d%%" is lower than expectation "%d%%"', $coverage, $checkCoverage));

            return 1;
        }

        return 0;
    }

    /**
     * Get coverage from given output.
     *
     * @param string $output
     *
     * @return null|float
     */
    private function getCoverage(string $output): ?float
    {
        // Lower and remove spaces
        $output = Strings::replace(Strings::lower($output), '/ /', '');

        if (Strings::contains($output, 'lines:') === false) {
            return null;
        }

        $match = Strings::match($output, '/lines:(\d+.\d+\d+)%/i') ?? [];

        return isset($match[1]) ? (float)$match[1] : null;
    }

    /**
     * Run and return process for given script.
     *
     * @param \Symfony\Component\Console\Input\InputInterface $input
     * @param \Symfony\Component\Console\Output\OutputInterface $output
     *
     * @return \Symfony\Component\Process\Process
     */
    private function runProcess(InputInterface $input, OutputInterface $output): Process
    {
        $script = \explode(' ', (string)$input->getArgument('script'));
        $process = new Process($script, null, null, null, 3600.00);

        $process->run(static function ($mode, $buffer) use ($output) {
            $output->write($buffer);
        });

        return $process;
    }
}
