<?php
declare(strict_types=1);

namespace EonX\EasyBankFiles\Parsing\Ach\ValueObject;

use EonX\EasyBankFiles\Parsing\Common\ValueObject\AbstractResult;

/**
 * @method string getAddendaRecordIndicator()
 * @method string getCheckDigit()
 * @method string getCode()
 * @method string getDfiAccountNumber()
 * @method string getDiscretionaryData()
 * @method string getDollarAmount()
 * @method string getIdentificationNumber()
 * @method string getIndividualOrReceivingCompanyName()
 * @method string getReceivingDfiId()
 * @method string getTraceNumber()
 * @method string getTransactionCode()
 */
final class EntryDetail extends AbstractResult
{
    private array $addenda = [];

    public function __construct(
        private readonly Batch $batch,
        ?array $data = null,
    ) {
        $batch->addEntryDetailRecord($this);

        parent::__construct($data);
    }

    public function addAddendaRecord(Addenda $addenda): void
    {
        $this->addenda[] = $addenda;
    }

    /**
     * @return \EonX\EasyBankFiles\Parsing\Ach\ValueObject\Addenda[]
     */
    public function getAddendaRecords(): array
    {
        return $this->addenda;
    }

    public function getBatch(): Batch
    {
        return $this->batch;
    }

    /**
     * Return object attributes.
     *
     * @return string[]
     */
    protected function initAttributes(): array
    {
        return [
            'addendaRecordIndicator',
            'checkDigit',
            'code',
            'dfiAccountNumber',
            'discretionaryData',
            'dollarAmount',
            'identificationNumber',
            'individualOrReceivingCompanyName',
            'receivingDfiId',
            'traceNumber',
            'transactionCode',
        ];
    }
}
