<?php
declare(strict_types=1);

namespace EonX\EasyBankFiles\Parsers\DirectEntryBatch;

use EonX\EasyBankFiles\Parsers\AbstractLineByLineParser;
use EonX\EasyBankFiles\Parsers\DirectEntryBatch\Results\Batch;
use EonX\EasyBankFiles\Parsers\DirectEntryBatch\Results\Batch\DescriptiveRecord;
use EonX\EasyBankFiles\Parsers\DirectEntryBatch\Results\Batch\FileTotalRecord;
use EonX\EasyBankFiles\Parsers\DirectEntryBatch\Results\Batch\PaymentDetailRecord;
use EonX\EasyBankFiles\Parsers\DirectEntryBatch\Results\Batch\RefusalDetailRecord;
use EonX\EasyBankFiles\Parsers\DirectEntryBatch\Results\Batch\ReturnDetailRecord;
use EonX\EasyBankFiles\Parsers\Error;

final class Parser extends AbstractLineByLineParser
{
    private const FINAL_LINE = 'End-Of-File';

    private const MIN_RECORD_LENGTH = [
        self::RECORD_TYPE_DESCRIPTIVE => 80,
        self::RECORD_TYPE_FILE_TOTAL => 80,
        self::RECORD_TYPE_PAYMENT => 120,
        self::RECORD_TYPE_REFUSAL => 120,
        self::RECORD_TYPE_RETURN => 120,
    ];

    private const RECORD_TYPE_DESCRIPTIVE = '0';

    private const RECORD_TYPE_FILE_TOTAL = '7';

    private const RECORD_TYPE_PAYMENT = '1';

    private const RECORD_TYPE_REFUSAL = '3';

    private const RECORD_TYPE_RETURN = '2';

    /**
     * @var \EonX\EasyBankFiles\Parsers\DirectEntryBatch\Results\Batch[]
     */
    private array $batches = [];

    private ?Batch $currentBatch = null;

    private array $errors = [];

    /**
     * @return \EonX\EasyBankFiles\Parsers\DirectEntryBatch\Results\Batch[]
     */
    public function getBatches(): array
    {
        return $this->batches;
    }

    /**
     * @return \EonX\EasyBankFiles\Parsers\Error[]
     */
    public function getErrors(): array
    {
        return $this->errors;
    }

    protected function process(): void
    {
        $contents = (array)\preg_split("/[\r\n]/", $this->contents);
        $lineNumber = 1;

        foreach ($contents as $line) {
            $line = \trim((string)$line);

            if ($line === '') {
                continue;
            }

            $this->processLine($lineNumber, $line);
            $lineNumber++;
        }
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
        if (isset($this->currentBatch) === false || $this->currentBatch->hasFileTotalRecord()) {
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
        /** @var string|false $bsb */
        $bsb = \substr($line, 1, 7);
        /** @var string|false $accountNumber */
        $accountNumber = \substr($line, 8, 9);
        /** @var string|false $transactionCode */
        $transactionCode = \substr($line, 18, 2);
        /** @var string|false $amount */
        $amount = \substr($line, 20, 10);
        /** @var string|false $accountName */
        $accountName = \substr($line, 30, 32);
        /** @var string|false $lodgmentReference */
        $lodgmentReference = \substr($line, 62, 18);
        /** @var string|false $traceBsb */
        $traceBsb = \substr($line, 80, 7);
        /** @var string|false $traceAccountNumber */
        $traceAccountNumber = \substr($line, 87, 9);
        /** @var string|false $remitterName */
        $remitterName = \substr($line, 96, 16);

        return [
            'accountName' => $accountName === false ? null : \trim($accountName),
            'accountNumber' => $accountNumber === false ? null : $accountNumber,
            'amount' => $amount === false ? null : $this->trimLeftZeros($amount),
            'bsb' => $bsb === false ? null : \str_replace('-', '', $bsb),
            'lodgmentReference' => $lodgmentReference === false ? null : \trim($lodgmentReference),
            'recordType' => $line[0] ?? '',
            'remitterName' => $remitterName === false ? null : \trim($remitterName),
            'traceAccountNumber' => $traceAccountNumber === false ? null : $traceAccountNumber,
            'traceBsb' => $traceBsb === false ? null : \str_replace('-', '', $traceBsb),
            'transactionCode' => $transactionCode === false ? null : $transactionCode,
        ];
    }

    private function processDescriptiveRecord(string $line): bool
    {
        /** @var string|false $reelSequenceNumber */
        $reelSequenceNumber = \substr($line, 18, 2);
        /** @var string|false $userFinancialInstitution */
        $userFinancialInstitution = \substr($line, 20, 3);
        /** @var string|false $nameOfUserSupplyingFile */
        $nameOfUserSupplyingFile = \substr($line, 30, 26);
        /** @var string|false $numberOfUserSupplyingFile */
        $numberOfUserSupplyingFile = \substr($line, 56, 6);
        /** @var string|false $descriptionOfEntries */
        $descriptionOfEntries = \substr($line, 62, 12);
        /** @var string|false $dateProcessed */
        $dateProcessed = \substr($line, 74, 6);

        return $this->setDescriptiveRecordToCurrentBatch(new DescriptiveRecord([
            'dateProcessed' => $dateProcessed === false ? null : $dateProcessed,
            'descriptionOfEntries' => $descriptionOfEntries === false ? null : \trim($descriptionOfEntries),
            'indicator' => $line[17] ?? '',
            'nameOfUserSupplyingFile' => $nameOfUserSupplyingFile === false ? null : \trim($nameOfUserSupplyingFile),
            'numberOfUserSupplyingFile' => $numberOfUserSupplyingFile === false ? null : $numberOfUserSupplyingFile,
            'reelSequenceNumber' => $reelSequenceNumber === false ? null : $reelSequenceNumber,
            'userFinancialInstitution' => $userFinancialInstitution === false ? null : $userFinancialInstitution,
        ]));
    }

    private function processFileTotalRecord(string $line): bool
    {
        /** @var string|false $bsb */
        $bsb = \substr($line, 1, 7);
        /** @var string|false $totalNetAmount */
        $totalNetAmount = \substr($line, 20, 10);
        /** @var string|false $totalCreditAmount */
        $totalCreditAmount = \substr($line, 30, 10);
        /** @var string|false $totalDebitAmount */
        $totalDebitAmount = \substr($line, 40, 10);
        /** @var string|false $totalRecordCount */
        $totalRecordCount = \substr($line, 74, 6);

        return $this->setFileTotalRecordToCurrentBatch(new FileTotalRecord([
            'bsb' => $bsb === false ? null : \str_replace('-', '', $bsb),
            'indicator' => $line[17] ?? '',
            'totalCreditAmount' => $totalCreditAmount === false ? null : $this->trimLeftZeros($totalCreditAmount),
            'totalDebitAmount' => $totalDebitAmount === false ? null : $this->trimLeftZeros($totalDebitAmount),
            'totalNetAmount' => $totalNetAmount === false ? null : $this->trimLeftZeros($totalNetAmount),
            'totalRecordCount' => $totalRecordCount === false ? null : $this->trimLeftZeros($totalRecordCount),
        ]));
    }

    private function processPaymentDetailRecord(string $line): bool
    {
        /** @var string|false $amountOfWithholdingTax */
        $amountOfWithholdingTax = \substr($line, 112, 8);

        return $this->addRecordToCurrentBatch(new PaymentDetailRecord(\array_merge(
            [
                'amountOfWithholdingTax' => $amountOfWithholdingTax === false
                    ? null
                    : $this->trimLeftZeros($amountOfWithholdingTax),
                'indicator' => $line[17] ?? '',
            ],
            $this->parseCommonRecordAttributes($line)
        )));
    }

    private function processRefusalDetailRecord(string $line): bool
    {
        /** @var string|false $originalDayOfReturn */
        $originalDayOfReturn = \substr($line, 112, 2);
        /** @var string|false $originalUserIdNumber */
        $originalUserIdNumber = \substr($line, 114, 6);

        return $this->addRecordToCurrentBatch(new RefusalDetailRecord(\array_merge(
            [
                'originalDayOfReturn' => $originalDayOfReturn === false ? null : $originalDayOfReturn,
                'originalUserIdNumber' => $originalUserIdNumber === false ? null : \trim($originalUserIdNumber),
                'refusalCode' => $line[17] ?? '',
            ],
            $this->parseCommonRecordAttributes($line)
        )));
    }

    private function processReturnDetailRecord(string $line): bool
    {
        /** @var string|false $originalDayOfProcessing */
        $originalDayOfProcessing = \substr($line, 112, 2);
        /** @var string|false $originalUserIdNumber */
        $originalUserIdNumber = \substr($line, 114, 6);

        return $this->addRecordToCurrentBatch(new ReturnDetailRecord(\array_merge(
            [
                'originalDayOfProcessing' => $originalDayOfProcessing === false ? null : $originalDayOfProcessing,
                'originalUserIdNumber' => $originalUserIdNumber === false ? null : \trim($originalUserIdNumber),
                'returnCode' => $line[17] ?? '',
            ],
            $this->parseCommonRecordAttributes($line)
        )));
    }

    private function setDescriptiveRecordToCurrentBatch(DescriptiveRecord $header): bool
    {
        $recordProcessed = $this->currentBatch === null;
        $this->currentBatch = (new Batch())->setDescriptiveRecord($header);

        return $recordProcessed;
    }

    private function setFileTotalRecordToCurrentBatch(FileTotalRecord $trailer): bool
    {
        if (isset($this->currentBatch) === false || $this->currentBatch->hasRecord() === false) {
            $this->currentBatch = null;

            return false;
        }
        $this->batches[] = $this->currentBatch->setFileTotalRecord($trailer);
        $this->currentBatch = null;

        return true;
    }
}
