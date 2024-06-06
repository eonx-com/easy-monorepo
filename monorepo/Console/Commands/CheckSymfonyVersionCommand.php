<?php
declare(strict_types=1);

namespace EonX\EasyMonorepo\Console\Commands;

use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'check-symfony-version',
)]
final class CheckSymfonyVersionCommand extends Command
{
    private const EXCLUDED_PACKAGES = [
        'symfony/deprecation-contracts',
        'symfony/event-dispatcher-contracts',
        'symfony/polyfill-ctype',
        'symfony/polyfill-intl-grapheme',
        'symfony/polyfill-intl-normalizer',
        'symfony/polyfill-mbstring',
        'symfony/polyfill-php72',
        'symfony/polyfill-php80',
        'symfony/polyfill-php83',
        'symfony/service-contracts',
    ];

    protected function configure(): void
    {
        $this
            ->addArgument('composerLockFile', InputArgument::REQUIRED, 'Path to the composer.lock file');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $symfonyStyleOutput = new SymfonyStyle($input, $output);
        $composerLockFile = $input->getArgument('composerLockFile');

        if (\file_exists($composerLockFile) === false) {
            $symfonyStyleOutput->error("The file $composerLockFile does not exist.");

            return Command::FAILURE;
        }

        $composerFile = \file_get_contents($composerLockFile);
        $composerData = (array)\json_decode($composerFile, true);

        if (isset($composerData['packages']) === false) {
            $symfonyStyleOutput->error("The file $composerLockFile does not contain valid composer.lock data.");

            return Command::FAILURE;
        }

        $symfonyPackages = \array_filter($composerData['packages'], static function ($package) {
            return \str_starts_with($package['name'], 'symfony/')
                && \in_array($package['name'], self::EXCLUDED_PACKAGES, true) === false;
        });

        $versions = \array_map(
            static function ($package) {
                $versionParts = \explode('.', $package['version']);

                return $versionParts[0] . '.' . $versionParts[1];
            },
            $symfonyPackages
        );

        $uniqueVersions = \array_unique($versions);

        if (count($uniqueVersions) > 1) {
            $symfonyStyleOutput->error("Symfony packages have different MAJOR.MINOR versions:");
            foreach ($symfonyPackages as $package) {
                $symfonyStyleOutput->writeln($package['name'] . ': ' . $package['version']);
            }

            return Command::FAILURE;
        }

        $symfonyStyleOutput
            ->success("All Symfony packages have the same MAJOR.MINOR version: " . \reset($uniqueVersions));

        return Command::SUCCESS;
    }
}
