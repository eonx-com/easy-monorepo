<?php

declare(strict_types=1);

namespace EonX\EasyCore\Csv;

use EonX\EasyCore\Csv\Exceptions\InvalidCsvFilenameException;

/**
 * @deprecated since 4.1, will be removed in 5.0. Use Eonx\EasyUtils\Csv\FromFileCsvContentsProvider.
 */
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
            || ($handle = \fopen($this->filename, 'r')) === false) {
            throw new InvalidCsvFilenameException(\sprintf(
                'File %s does not exist or is not readable',
                $this->filename,
            ));
        }

        while (($row = \fgetcsv($handle)) !== false) {
            yield $row;
        }

        \fclose($handle);
    }
}
