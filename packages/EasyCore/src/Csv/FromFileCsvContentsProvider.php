<?php

declare(strict_types=1);

namespace EonX\EasyCore\Csv;

use EonX\EasyCore\Csv\Exceptions\InvalidCsvFilenameException;

final class FromFileCsvContentsProvider implements CsvContentsProviderInterface
{
    /**
     * @var string
     */
    private $filename;

    public function __construct(string $filename)
    {
        $this->filename = $filename;
    }

    /**
     * @return iterable<mixed>
     *
     * @throws \EonX\EasyCore\Csv\Exceptions\InvalidCsvFilenameException
     */
    public function getContents(): iterable
    {
        if (\file_exists($this->filename) === false
            || \is_readable($this->filename) === false
            || \fopen($this->filename, 'r') === false) {
            throw new InvalidCsvFilenameException(\sprintf(
                'File %s does not exist or is not readable',
                $this->filename
            ));
        }

        $handle = \fopen($this->filename, 'r');
        $row = \fgetcsv($handle);

        while ($row !== false) {
            yield $row;
        }

        \fclose($handle);
    }
}
