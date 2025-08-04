<?php
declare(strict_types=1);

namespace EonX\EasyBankFiles\Parsing\Ach\Parser;

use EonX\EasyBankFiles\Parsing\Ach\ValueObject\Addenda;
use EonX\EasyBankFiles\Parsing\Ach\ValueObject\Batch;
use EonX\EasyBankFiles\Parsing\Ach\ValueObject\BatchControl;
use EonX\EasyBankFiles\Parsing\Ach\ValueObject\BatchHeader;
use EonX\EasyBankFiles\Parsing\Ach\ValueObject\EntryDetail;
use EonX\EasyBankFiles\Parsing\Ach\ValueObject\FileControl;
use EonX\EasyBankFiles\Parsing\Ach\ValueObject\FileHeader;
use EonX\EasyBankFiles\Parsing\Common\Parser\AbstractLineByLineParser;
use EonX\EasyBankFiles\Parsing\Common\ValueObject\Error;

final class AchParser extends AbstractLineByLineParser
{
    private const CODE_ADDENDA = '7';

    private const CODE_BATCH_CONTROL = '8';

    private const CODE_BATCH_HEADER = '5';

    private const CODE_ENTRY_DETAIL = '6';

    private const CODE_FILE_CONTROL = '9';

    private const CODE_FILE_HEADER = '1';

    /**
     * @var \EonX\EasyBankFiles\Parsing\Ach\ValueObject\Batch[]
     */
    private array $batches = [];

    private Batch $currentBatch;

    private EntryDetail $currentEntryDetail;

    private int $currentLineNumber;

    /**
     * @var \EonX\EasyBankFiles\Parsing\Ach\ValueObject\EntryDetail[]
     */
    private array $entryDetailRecords = [];

    /**
     * @var \EonX\EasyBankFiles\Parsing\Common\ValueObject\Error[]
     */
    private array $errors = [];

    private FileControl $fileControl;

    private FileHeader $fileHeader;

    /**
     * @return \EonX\EasyBankFiles\Parsing\Ach\ValueObject\Batch[]
     */
    public function getBatches(): array
    {
        return $this->batches;
    }

    /**
     * @return \EonX\EasyBankFiles\Parsing\Ach\ValueObject\EntryDetail[]
     */
    public function getEntryDetailRecords(): array
    {
        return $this->entryDetailRecords;
    }

    /**
     * @return \EonX\EasyBankFiles\Parsing\Common\ValueObject\Error[]
     */
    public function getErrors(): array
    {
        return $this->errors;
    }

    public function getFileControl(): FileControl
    {
        return $this->fileControl;
    }

    public function getFileHeader(): FileHeader
    {
        return $this->fileHeader;
    }

    protected function processLine(int $lineNumber, string $line): void
    {
        $code = \substr($line, 0, 1);

        // Set current line number
        $this->currentLineNumber = $lineNumber;

        // If current code not valid, create error and skip to next line
        if ($this->isCodeValid($code) === false) {
            $this->addError($line);

            return;
        }

        switch ($code) {
            case self::CODE_ADDENDA:
                $this->setAddenda($line);

                break;

            case self::CODE_BATCH_CONTROL:
                $this->setBatchControl($line);

                break;

            case self::CODE_BATCH_HEADER:
                $batch = new Batch();
                $this->batches[] = $batch;
                $this->currentBatch = $batch;

                $this->setBatchHeader($line);

                break;

            case self::CODE_ENTRY_DETAIL:
                $this->setEntryDetail($line);

                break;

            case self::CODE_FILE_CONTROL:
                $this->setFileControl($line);

                break;

            case self::CODE_FILE_HEADER:
                $this->setFileHeader($line);

                break;
        }
    }

    private function addError(string $line): void
    {
        $this->errors[] = new Error($this->setItem($line));
    }

    /**
     * Check if given code is valid.
     */
    private function isCodeValid(string $code): bool
    {
        $codes = [
            self::CODE_ADDENDA,
            self::CODE_BATCH_HEADER,
            self::CODE_BATCH_CONTROL,
            self::CODE_ENTRY_DETAIL,
            self::CODE_FILE_HEADER,
            self::CODE_FILE_CONTROL,
        ];

        return \in_array($code, $codes, true);
    }

    private function setAddenda(string $line): void
    {
        $this->currentEntryDetail->addAddendaRecord(new Addenda([
            'addendaSequenceNumber' => \substr($line, 83, 4),
            'addendaTypeCode' => \substr($line, 1, 2),
            'code' => \substr($line, 0, 1),
            'entryDetailSequenceNumber' => \substr($line, 87, 7),
            'paymentRelatedInformation' => \substr($line, 3, 80),
        ]));
    }

    private function setBatchControl(string $line): void
    {
        $this->currentBatch->setControl(new BatchControl([
            'batchNumber' => \substr($line, 87, 7),
            'code' => \substr($line, 0, 1),
            'companyIdentification' => \substr($line, 44, 10),
            'entryAddendaCount' => \substr($line, 4, 6),
            'entryHash' => \substr($line, 10, 10),
            'messageAuthenticationCode' => \substr($line, 54, 19),
            'originatingDfiIdentification' => \substr($line, 79, 8),
            'reserved' => \substr($line, 73, 6),
            'serviceClassCode' => \substr($line, 1, 3),
            'totalCreditAmount' => \substr($line, 32, 12),
            'totalDebitAmount' => \substr($line, 20, 12),
        ]));
    }

    private function setBatchHeader(string $line): void
    {
        $this->currentBatch->setHeader(new BatchHeader([
            'batchNumber' => \substr($line, 87, 7),
            'code' => \substr($line, 0, 1),
            'companyDescriptiveDate' => \substr($line, 63, 6),
            'companyDiscretionaryData' => \substr($line, 20, 20),
            'companyEntryDescription' => \substr($line, 53, 10),
            'companyIdentification' => \substr($line, 40, 10),
            'companyName' => \substr($line, 4, 16),
            'effectiveEntryDate' => \substr($line, 69, 6),
            'originatingDfiIdentification' => \substr($line, 79, 8),
            'originatorStatusCode' => \substr($line, 78, 1),
            'serviceClassCode' => \substr($line, 1, 3),
            'settlementDate' => \substr($line, 75, 3),
            'standardEntryClassCode' => \substr($line, 50, 3),
        ]));
    }

    private function setEntryDetail(string $line): void
    {
        $data = [
            'addendaRecordIndicator' => \substr($line, 78, 1),
            'checkDigit' => \substr($line, 11, 1),
            'code' => \substr($line, 0, 1),
            'dfiAccountNumber' => \substr($line, 12, 17),
            'discretionaryData' => \substr($line, 76, 2),
            'dollarAmount' => \substr($line, 29, 10),
            'identificationNumber' => \substr($line, 39, 15),
            'individualOrReceivingCompanyName' => \substr($line, 54, 22),
            'receivingDfiId' => \substr($line, 3, 8),
            'traceNumber' => \substr($line, 79, 15),
            'transactionCode' => \substr($line, 1, 2),
        ];

        $entryDetail = new EntryDetail($this->currentBatch, $data);

        $this->currentEntryDetail = $entryDetail;
        $this->entryDetailRecords[] = $entryDetail;
    }

    private function setFileControl(string $line): void
    {
        $this->fileControl = new FileControl([
            'batchCount' => \substr($line, 1, 6),
            'blockCount' => \substr($line, 7, 6),
            'code' => \substr($line, 0, 1),
            'entryAddendaCount' => \substr($line, 13, 8),
            'entryHash' => \substr($line, 21, 10),
            'reserved' => \substr($line, 55, 39),
            'totalCreditAmount' => \substr($line, 43, 12),
            'totalDebitAmount' => \substr($line, 31, 12),
        ]);
    }

    private function setFileHeader(string $line): void
    {
        $this->fileHeader = new FileHeader([
            'blockingFactor' => \substr($line, 37, 2),
            'code' => \substr($line, 0, 1),
            'fileCreationDate' => \substr($line, 23, 6),
            'fileCreationTime' => \substr($line, 29, 4),
            'fileIdModifier' => \substr($line, 33, 1),
            'formatCode' => \substr($line, 39, 1),
            'immediateDestination' => \substr($line, 3, 10),
            'immediateDestinationName' => \substr($line, 40, 23),
            'immediateOrigin' => \substr($line, 13, 10),
            'immediateOriginName' => \substr($line, 63, 23),
            'priorityCode' => \substr($line, 1, 2),
            'recordSize' => \substr($line, 34, 3),
            'referenceCode' => \substr($line, 86, 8),
        ]);
    }

    /**
     * Structure item content with line number.
     */
    private function setItem(string $line): array
    {
        // Sanitise line before setting item
        return [
            'line' => \str_replace('/', '', $line),
            'lineNumber' => $this->currentLineNumber,
        ];
    }
}
