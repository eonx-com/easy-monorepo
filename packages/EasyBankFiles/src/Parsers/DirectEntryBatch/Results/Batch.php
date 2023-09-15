<?php
declare(strict_types=1);

namespace EonX\EasyBankFiles\Parsers\DirectEntryBatch\Results;

use EonX\EasyBankFiles\Parsers\DirectEntryBatch\Results\Batch\DescriptiveRecord;
use EonX\EasyBankFiles\Parsers\DirectEntryBatch\Results\Batch\FileTotalRecord;
use EonX\EasyBankFiles\Parsers\DirectEntryBatch\Results\Batch\PaymentDetailRecord;
use EonX\EasyBankFiles\Parsers\DirectEntryBatch\Results\Batch\RefusalDetailRecord;
use EonX\EasyBankFiles\Parsers\DirectEntryBatch\Results\Batch\ReturnDetailRecord;

final class Batch
{
    private DescriptiveRecord $descriptiveRecord;

    private FileTotalRecord $fileTotalRecord;

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

    public function hasRecord(): bool
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
