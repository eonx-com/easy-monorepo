<?php
declare(strict_types=1);

namespace EonX\EasyUtils\Csv;

use EonX\EasyUtils\Csv\Exceptions\InvalidCsvFilenameException;

final class FromFileCsvContentsProvider implements CsvContentsProviderInterface
{
    public function __construct(
        private readonly string $filename,
    ) {
    }

    /**
     * @throws \EonX\EasyUtils\Csv\Exceptions\InvalidCsvFilenameException
     */
    public function getContents(): iterable
    {
        if (\file_exists($this->filename) === false
            || \is_readable($this->filename) === false
            || ($handle = \fopen($this->filename, 'r')) === false) {
            throw new InvalidCsvFilenameException(\sprintf(
                'File %s does not exist or is not readable',
                $this->filename
            ));
        }

        while (($row = \fgetcsv($handle)) !== false) {
            yield $row;
        }

        \fclose($handle);
    }
}
