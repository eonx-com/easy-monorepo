<?php
declare(strict_types=1);

namespace EonX\EasyBankFiles\Parsing\Ach\ValueObject;

final class Batch
{
    private BatchControl $control;

    /**
     * @var \EonX\EasyBankFiles\Parsing\Ach\ValueObject\EntryDetail[]
     */
    private array $entryDetailRecords = [];

    private BatchHeader $header;

    public function addEntryDetailRecord(EntryDetail $entryDetail): void
    {
        $this->entryDetailRecords[] = $entryDetail;
    }

    public function getControl(): BatchControl
    {
        return $this->control;
    }

    /**
     * @return \EonX\EasyBankFiles\Parsing\Ach\ValueObject\EntryDetail[]
     */
    public function getEntryDetailRecords(): array
    {
        return $this->entryDetailRecords;
    }

    public function getHeader(): BatchHeader
    {
        return $this->header;
    }

    public function setControl(BatchControl $control): void
    {
        $this->control = $control;
    }

    public function setHeader(BatchHeader $header): void
    {
        $this->header = $header;
    }
}
