<?php
declare(strict_types=1);

namespace EonX\EasyTest\Console\Commands;

use Carbon\Carbon;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Finder\Finder;
use Symfony\Component\Uid\UuidV4;

final class MlsFormatFilesCommand extends Command
{
    protected static $defaultName = 'mls:format-files';

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $from = __DIR__ . '/../../../../../secret/mls/origin';
        $to = __DIR__ . '/../../../../../secret/mls/destination';

        $style = new SymfonyStyle($input, $output);
        $finder = (new Finder())->in($from)->files();
        $filesystem = new Filesystem();

        foreach ($finder as $file) {
            $filename = $file->getRealPath();
            $destinationFilename = \sprintf('%s/%s', $to, $file->getFilename());

            $style->section(\sprintf('Formatting records from file %s', $filename));

            if ($filesystem->exists($destinationFilename)) {
                $filesystem->remove($destinationFilename);
            }

            $handle = \fopen($filename, 'r');
            $destinationHandle = \fopen($destinationFilename, 'w+');
            $rowNumber = 0;
            $total = 0;
            $start = Carbon::now();

            while (($row = \fgetcsv($handle)) !== false) {
                $rowNumber++;

                // Ignore empty lines
                if (\is_string($row[0] ?? null) === false || ($row[0] ?? null) === '') {
                    continue;
                }

                if (isset($row[1]) === false) {
                    $style->error(\sprintf('Line %d is malformed, check the file', $rowNumber));
                    return self::FAILURE;
                }

                $row = \array_map(static fn(string $value) => \trim($value), $row);

                if ($rowNumber === 1) {
                    \array_unshift($row, 'id');
                    $row[] = 'created_at';
                    $row[] = 'updated_at';
                }

                if ($rowNumber > 1) {
                    \array_unshift($row, UuidV4::v4()->__toString());
                    $row[] = Carbon::now('UTC')->format('Y-m-d H:i:s');
                    $row[] = Carbon::now('UTC')->format('Y-m-d H:i:s');
                }

                \fputcsv($destinationHandle, $row);

                $total++;
            }

            $end = Carbon::now();
            $diff = \str_replace('after', '', $end->diffForHumans($start, null, true));

            $total > 0
                ? $style->success(\sprintf('Formatted %d records from %s, took %s', $total, $file->getFilename(), $diff))
                : $style->warning(\sprintf('No records formatted from %s, check content of the file', $file->getFilename()));

            \fclose($handle);
            \fclose($destinationHandle);
        }

        return self::SUCCESS;
    }
}
