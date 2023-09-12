<?php
declare(strict_types=1);

namespace EonX\EasyBankFiles\Parsers\DirectEntryBatch\Results;

use EonX\EasyBankFiles\Parsers\DirectEntryBatch\Results\Batch\DescriptiveRecord;
use EonX\EasyBankFiles\Parsers\DirectEntryBatch\Results\Batch\FileTotalRecordRecord;
use EonX\EasyBankFiles\Parsers\DirectEntryBatch\Results\Batch\PaymentDetailRecord;
use EonX\EasyBankFiles\Parsers\DirectEntryBatch\Results\Batch\RefusalDetailRecord;
use EonX\EasyBankFiles\Parsers\DirectEntryBatch\Results\Batch\ReturnDetailRecord;

final class Batch
{
    private DescriptiveRecord $descriptiveRecord;

    private FileTotalRecordRecord $fileTotalRecordRecord;

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

    public function getFileTotalRecordRecord(): FileTotalRecordRecord
    {
        return $this->fileTotalRecordRecord;
    }

    public function getRecords(): array
    {
        return $this->records;
    }

    public function hasTrailer(): bool
    {
        return isset($this->fileTotalRecordRecord) === true;
    }

    public function hasTransaction(): bool
    {
        return \count($this->records) > 0;
    }

    public function setDescriptiveRecord(DescriptiveRecord $descriptiveRecord): self
    {
        $this->descriptiveRecord = $descriptiveRecord;

        return $this;
    }

    public function setFileTotalRecordRecord(FileTotalRecordRecord $fileTotalRecordRecord): self
    {
        $this->fileTotalRecordRecord = $fileTotalRecordRecord;

        return $this;
    }
}
