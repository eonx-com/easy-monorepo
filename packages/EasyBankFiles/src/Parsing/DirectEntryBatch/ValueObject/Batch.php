<?php
declare(strict_types=1);

namespace EonX\EasyBankFiles\Parsing\DirectEntryBatch\ValueObject;

final class Batch
{
    private DescriptiveRecord $descriptiveRecord;

    private FileTotalRecord $fileTotalRecord;

    /**
     * @var array<int, \EonX\EasyBankFiles\Parsing\DirectEntryBatch\ValueObject\PaymentDetailRecord|\EonX\EasyBankFiles\Parsing\DirectEntryBatch\ValueObject\ReturnDetailRecord|\EonX\EasyBankFiles\Parsing\DirectEntryBatch\ValueObject\RefusalDetailRecord>
     */
    private array $records = [];

    public function addRecord(
        PaymentDetailRecord|ReturnDetailRecord|RefusalDetailRecord $transaction,
    ): self {
        $this->records[] = $transaction;

        return $this;
    }

    public function getDescriptiveRecord(): DescriptiveRecord
    {
        return $this->descriptiveRecord;
    }

    public function getFileTotalRecord(): FileTotalRecord
    {
        return $this->fileTotalRecord;
    }

    public function getRecords(): array
    {
        return $this->records;
    }

    public function hasFileTotalRecord(): bool
    {
        return isset($this->fileTotalRecord) === true;
    }

    public function hasRecords(): bool
    {
        return \count($this->records) > 0;
    }

    public function setDescriptiveRecord(DescriptiveRecord $descriptiveRecord): self
    {
        $this->descriptiveRecord = $descriptiveRecord;

        return $this;
    }

    public function setFileTotalRecord(FileTotalRecord $fileTotalRecord): self
    {
        $this->fileTotalRecord = $fileTotalRecord;

        return $this;
    }
}
