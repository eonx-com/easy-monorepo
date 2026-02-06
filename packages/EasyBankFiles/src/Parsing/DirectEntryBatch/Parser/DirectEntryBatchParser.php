<?php
declare(strict_types=1);

namespace EonX\EasyBankFiles\Parsing\DirectEntryBatch\Parser;

use EonX\EasyBankFiles\Parsing\Common\Parser\AbstractLineByLineParser;
use EonX\EasyBankFiles\Parsing\Common\ValueObject\Error;
use EonX\EasyBankFiles\Parsing\DirectEntryBatch\ValueObject\Batch;
use EonX\EasyBankFiles\Parsing\DirectEntryBatch\ValueObject\DescriptiveRecord;
use EonX\EasyBankFiles\Parsing\DirectEntryBatch\ValueObject\FileTotalRecord;
use EonX\EasyBankFiles\Parsing\DirectEntryBatch\ValueObject\PaymentDetailRecord;
use EonX\EasyBankFiles\Parsing\DirectEntryBatch\ValueObject\RefusalDetailRecord;
use EonX\EasyBankFiles\Parsing\DirectEntryBatch\ValueObject\ReturnDetailRecord;

final class DirectEntryBatchParser extends AbstractLineByLineParser
{
    private const string FINAL_LINE = 'End-Of-File';

    private const array MIN_RECORD_LENGTH = [
        self::RECORD_TYPE_DESCRIPTIVE => 80,
        self::RECORD_TYPE_FILE_TOTAL => 80,
        self::RECORD_TYPE_PAYMENT => 120,
        self::RECORD_TYPE_REFUSAL => 120,
        self::RECORD_TYPE_RETURN => 120,
    ];

    private const string RECORD_TYPE_DESCRIPTIVE = '0';

    private const string RECORD_TYPE_FILE_TOTAL = '7';

    private const string RECORD_TYPE_PAYMENT = '1';

    private const string RECORD_TYPE_REFUSAL = '3';

    private const string RECORD_TYPE_RETURN = '2';

    /**
     * @var \EonX\EasyBankFiles\Parsing\DirectEntryBatch\ValueObject\Batch[]
     */
    private array $batches = [];

    private ?Batch $currentBatch = null;

    /**
     * @var \EonX\EasyBankFiles\Parsing\Common\ValueObject\Error[]
     */
    private array $errors = [];

    /**
     * @return \EonX\EasyBankFiles\Parsing\DirectEntryBatch\ValueObject\Batch[]
     */
    public function getBatches(): array
    {
        return $this->batches;
    }

    /**
     * @return \EonX\EasyBankFiles\Parsing\Common\ValueObject\Error[]
     */
    public function getErrors(): array
    {
        return $this->errors;
    }

    protected function processLine(int $lineNumber, string $line): void
    {
        $code = $line[0] ?? self::EMPTY_LINE_CODE;

        if ($this->checkMinLength($line, $code)) {
            $lineProcessingResult = match ($code) {
                self::RECORD_TYPE_DESCRIPTIVE => $this->processDescriptiveRecord($line),
                self::RECORD_TYPE_FILE_TOTAL => $this->processFileTotalRecord($line),
                self::RECORD_TYPE_PAYMENT => $this->processPaymentDetailRecord($line),
                self::RECORD_TYPE_RETURN => $this->processReturnDetailRecord($line),
                self::RECORD_TYPE_REFUSAL => $this->processRefusalDetailRecord($line),
                default => $this->isFinalLine($line)
            };

            if ($lineProcessingResult === true) {
                return;
            }
        }

        $this->errors[] = new Error(\compact('line', 'lineNumber'));
    }

    private function addRecordToCurrentBatch(
        PaymentDetailRecord|ReturnDetailRecord|RefusalDetailRecord $record,
    ): bool {
        if ($this->currentBatch === null || $this->currentBatch->hasFileTotalRecord()) {
            $this->currentBatch = null;

            return false;
        }
        $this->currentBatch->addRecord($record);

        return true;
    }

    private function checkMinLength(string $line, string $code): bool
    {
        return isset(self::MIN_RECORD_LENGTH[$code]) === false || \strlen($line) >= self::MIN_RECORD_LENGTH[$code];
    }

    private function isFinalLine(string $line): bool
    {
        return (\trim($line) === self::FINAL_LINE || \trim($line, '9') === '') &&
            ($this->currentBatch === null || $this->currentBatch->hasFileTotalRecord());
    }

    private function parseCommonRecordAttributes(string $line): array
    {
        $recordType = $line[0] ?? '';
        $bsb = \substr($line, 1, 7);
        $accountNumber = \substr($line, 8, 9);
        $transactionCode = \substr($line, 18, 2);
        $amount = \substr($line, 20, 10);
        $accountName = \substr($line, 30, 32);
        $lodgmentReference = \substr($line, 62, 18);
        $traceBsb = \substr($line, 80, 7);
        $traceAccountNumber = \substr($line, 87, 9);
        $remitterName = \substr($line, 96, 16);

        return [
            'accountName' => \trim($accountName),
            'accountNumber' => $accountNumber,
            'amount' => $this->trimLeftZeros($amount),
            'bsb' => \str_replace('-', '', $bsb),
            'lodgmentReference' => \trim($lodgmentReference),
            'recordType' => $recordType,
            'remitterName' => \trim($remitterName),
            'traceAccountNumber' => $traceAccountNumber,
            'traceBsb' => \str_replace('-', '', $traceBsb),
            'transactionCode' => $transactionCode,
        ];
    }

    private function processDescriptiveRecord(string $line): bool
    {
        $indicator = $line[17] ?? '';
        $reelSequenceNumber = \substr($line, 18, 2);
        $userFinancialInstitution = \substr($line, 20, 3);
        $nameOfUserSupplyingFile = \substr($line, 30, 26);
        $numberOfUserSupplyingFile = \substr($line, 56, 6);
        $descriptionOfEntries = \substr($line, 62, 12);
        $dateProcessed = \substr($line, 74, 6);

        return $this->setDescriptiveRecordToCurrentBatch(new DescriptiveRecord([
            'dateProcessed' => $dateProcessed,
            'descriptionOfEntries' => \trim($descriptionOfEntries),
            'indicator' => $indicator,
            'nameOfUserSupplyingFile' => \trim($nameOfUserSupplyingFile),
            'numberOfUserSupplyingFile' => $numberOfUserSupplyingFile,
            'reelSequenceNumber' => $reelSequenceNumber,
            'userFinancialInstitution' => $userFinancialInstitution,
        ]));
    }

    private function processFileTotalRecord(string $line): bool
    {
        $bsb = \substr($line, 1, 7);
        $indicator = $line[17] ?? '';
        $totalNetAmount = \substr($line, 20, 10);
        $totalCreditAmount = \substr($line, 30, 10);
        $totalDebitAmount = \substr($line, 40, 10);
        $totalRecordCount = \substr($line, 74, 6);

        return $this->setFileTotalRecordToCurrentBatch(new FileTotalRecord([
            'bsb' => \str_replace('-', '', $bsb),
            'indicator' => $indicator,
            'totalCreditAmount' => $this->trimLeftZeros($totalCreditAmount),
            'totalDebitAmount' => $this->trimLeftZeros($totalDebitAmount),
            'totalNetAmount' => $this->trimLeftZeros($totalNetAmount),
            'totalRecordCount' => $this->trimLeftZeros($totalRecordCount),
        ]));
    }

    private function processPaymentDetailRecord(string $line): bool
    {
        $indicator = $line[17] ?? '';
        $amountOfWithholdingTax = \substr($line, 112, 8);

        return $this->addRecordToCurrentBatch(new PaymentDetailRecord(\array_merge(
            [
                'amountOfWithholdingTax' => $this->trimLeftZeros($amountOfWithholdingTax),
                'indicator' => $indicator,
            ],
            $this->parseCommonRecordAttributes($line)
        )));
    }

    private function processRefusalDetailRecord(string $line): bool
    {
        $refusalCode = $line[17] ?? '';
        $originalDayOfReturn = \substr($line, 112, 2);
        $originalUserIdNumber = \substr($line, 114, 6);

        return $this->addRecordToCurrentBatch(new RefusalDetailRecord(\array_merge(
            [
                'originalDayOfReturn' => $originalDayOfReturn,
                'originalUserIdNumber' => \trim($originalUserIdNumber),
                'refusalCode' => $refusalCode,
            ],
            $this->parseCommonRecordAttributes($line)
        )));
    }

    private function processReturnDetailRecord(string $line): bool
    {
        $returnCode = $line[17] ?? '';
        $originalDayOfProcessing = \substr($line, 112, 2);
        $originalUserIdNumber = \substr($line, 114, 6);

        return $this->addRecordToCurrentBatch(new ReturnDetailRecord(\array_merge(
            [
                'originalDayOfProcessing' => $originalDayOfProcessing,
                'originalUserIdNumber' => \trim($originalUserIdNumber),
                'returnCode' => $returnCode,
            ],
            $this->parseCommonRecordAttributes($line)
        )));
    }

    private function setDescriptiveRecordToCurrentBatch(DescriptiveRecord $header): bool
    {
        $recordProcessed = $this->currentBatch === null;
        $this->currentBatch = new Batch()
->setDescriptiveRecord($header);

        return $recordProcessed;
    }

    private function setFileTotalRecordToCurrentBatch(FileTotalRecord $trailer): bool
    {
        if ($this->currentBatch === null || $this->currentBatch->hasRecords() === false) {
            $this->currentBatch = null;

            return false;
        }
        $this->batches[] = $this->currentBatch->setFileTotalRecord($trailer);
        $this->currentBatch = null;

        return true;
    }
}
