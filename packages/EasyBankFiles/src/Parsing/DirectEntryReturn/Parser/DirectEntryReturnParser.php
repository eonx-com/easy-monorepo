<?php
declare(strict_types=1);

namespace EonX\EasyBankFiles\Parsing\DirectEntryReturn\Parser;

use EonX\EasyBankFiles\Parsing\Common\Parser\AbstractLineByLineParser;
use EonX\EasyBankFiles\Parsing\Common\ValueObject\Error;
use EonX\EasyBankFiles\Parsing\DirectEntryReturn\ValueObject\DetailRecord;
use EonX\EasyBankFiles\Parsing\DirectEntryReturn\ValueObject\HeaderRecord;
use EonX\EasyBankFiles\Parsing\DirectEntryReturn\ValueObject\TrailerRecord;

final class DirectEntryReturnParser extends AbstractLineByLineParser
{
    private const MIN_DETAIL_RECORD_LINE_LENGTH = 120;

    private const MIN_HEADER_RECORD_LINE_LENGTH = 80;

    private const MIN_TRAILER_RECORD_LINE_LENGTH = 80;

    private const RECORD_TYPE_DETAIL_1 = '1';

    private const RECORD_TYPE_DETAIL_2 = '2';

    private const RECORD_TYPE_HEADER = '0';

    private const RECORD_TYPE_TRAILER = '7';

    /**
     * @var \EonX\EasyBankFiles\Parsing\DirectEntryReturn\ValueObject\DetailRecord[]
     */
    private array $detailRecords = [];

    /**
     * @var \EonX\EasyBankFiles\Parsing\Common\ValueObject\Error[] $errors
     */
    private array $errors = [];

    private HeaderRecord $headerRecord;

    private TrailerRecord $trailerRecord;

    /**
     * @return \EonX\EasyBankFiles\Parsing\DirectEntryReturn\ValueObject\DetailRecord[]
     */
    public function getDetailRecords(): array
    {
        return $this->detailRecords;
    }

    /**
     * @return \EonX\EasyBankFiles\Parsing\Common\ValueObject\Error[]
     */
    public function getErrors(): array
    {
        return $this->errors;
    }

    /**
     * Get header record.
     */
    public function getHeaderRecord(): HeaderRecord
    {
        return $this->headerRecord;
    }

    /**
     * Get trailer record.
     */
    public function getTrailerRecord(): TrailerRecord
    {
        return $this->trailerRecord;
    }

    protected function processLine(int $lineNumber, string $line): void
    {
        // Code is the first character in line
        $code = $line[0] ?? self::EMPTY_LINE_CODE;
        $lineLength = \strlen($line);

        if ($code === self::RECORD_TYPE_HEADER && $lineLength >= self::MIN_HEADER_RECORD_LINE_LENGTH) {
            $this->headerRecord = $this->processHeaderRecord($line);

            return;
        }

        if ($code === self::RECORD_TYPE_TRAILER && $lineLength >= self::MIN_TRAILER_RECORD_LINE_LENGTH) {
            $this->trailerRecord = $this->processTrailerRecord($line);

            return;
        }

        if (
            ($code === self::RECORD_TYPE_DETAIL_1 || $code === self::RECORD_TYPE_DETAIL_2) &&
            $lineLength >= self::MIN_DETAIL_RECORD_LINE_LENGTH
        ) {
            $this->detailRecords[] = $this->processDetailRecord($line);

            return;
        }

        $this->errors[] = new Error(\compact('line', 'lineNumber'));
    }

    /**
     * Process transaction block of line.
     */
    private function processDetailRecord(string $line): DetailRecord
    {
        /** @var string|false $accountName */
        $accountName = \substr($line, 30, 32);
        /** @var string|false $accountNumber */
        $accountNumber = \substr($line, 8, 9);
        /** @var string|false $amount */
        $amount = \substr($line, 20, 10);
        /** @var string|false $bsb */
        $bsb = \substr($line, 1, 7);
        /** @var string|false $lodgmentReference */
        $lodgmentReference = \substr($line, 62, 18);
        /** @var string|false $originalDayOfProcessing */
        $originalDayOfProcessing = \substr($line, 112, 2);
        /** @var string|false $originalUserIdNumber */
        $originalUserIdNumber = \substr($line, 114, 6);
        /** @var string|false $remitterName */
        $remitterName = \substr($line, 96, 16);
        /** @var string|false $traceAccountNumber */
        $traceAccountNumber = \substr($line, 87, 9);
        /** @var string|false $traceBsb */
        $traceBsb = \substr($line, 80, 7);
        /** @var string|false $transactionCode */
        $transactionCode = \substr($line, 18, 2);

        return new DetailRecord([
            'accountName' => $accountName === false ? null : \trim($accountName),
            'accountNumber' => $accountNumber === false ? null : $accountNumber,
            'amount' => $amount === false ? null : $this->trimLeftZeros($amount),
            'bsb' => $bsb === false ? null : \str_replace('-', '', $bsb),
            'lodgmentReference' => $lodgmentReference === false ? null : \trim($lodgmentReference),
            'originalDayOfProcessing' => $originalDayOfProcessing === false ? null : \trim($originalDayOfProcessing),
            'originalUserIdNumber' => $originalUserIdNumber === false ? null : \trim($originalUserIdNumber),
            'recordType' => $line[0] ?? '',
            'remitterName' => $remitterName === false ? null : \trim($remitterName),
            'returnCode' => $line[17] ?? '',
            'traceAccountNumber' => $traceAccountNumber === false ? null : $traceAccountNumber,
            'traceBsb' => $traceBsb === false ? null : \str_replace('-', '', $traceBsb),
            'transactionCode' => $transactionCode === false ? null : $transactionCode,
        ]);
    }

    /**
     * Process header block of line.
     */
    private function processHeaderRecord(string $line): HeaderRecord
    {
        /** @var string|false $dateProcessed */
        $dateProcessed = \substr($line, 74, 6);
        /** @var string|false $description */
        $description = \substr($line, 62, 12);
        /** @var string|false $mnemonicOfFinancialInstitution */
        $mnemonicOfFinancialInstitution = \substr($line, 20, 3);
        /** @var string|false $directEntryUserId */
        $directEntryUserId = \substr($line, 56, 6);
        /** @var string|false $mnemonicOfSendingMember */
        $mnemonicOfSendingMember = \substr($line, 30, 26);
        /** @var string|false $reelSequenceNumber */
        $reelSequenceNumber = \substr($line, 18, 2);

        return new HeaderRecord([
            'dateProcessed' => $dateProcessed === false ? null : $dateProcessed,
            'description' => $description === false ? null : \trim($description),
            'directEntryUserId' => $directEntryUserId === false ? null : $directEntryUserId,
            'mnemonicOfFinancialInstitution' => $mnemonicOfFinancialInstitution === false
                ? null
                : $mnemonicOfFinancialInstitution,
            'mnemonicOfSendingMember' => $mnemonicOfSendingMember === false ? null : \trim($mnemonicOfSendingMember),
            'reelSequenceNumber' => $reelSequenceNumber === false ? null : $reelSequenceNumber,
        ]);
    }

    /**
     * Process trailer block of line.
     */
    private function processTrailerRecord(string $line): TrailerRecord
    {
        /** @var string|false $bsb */
        $bsb = \substr($line, 1, 7);
        /** @var string|false $totalRecordCount */
        $totalRecordCount = \substr($line, 74, 6);
        /** @var string|false $totalNetAmount */
        $totalNetAmount = \substr($line, 20, 10);
        /** @var string|false $totalCreditAmount */
        $totalCreditAmount = \substr($line, 30, 10);
        /** @var string|false $totalDebitAmount */
        $totalDebitAmount = \substr($line, 40, 10);

        return new TrailerRecord([
            'bsb' => $bsb === false ? null : \str_replace('-', '', $bsb),
            'totalCreditAmount' => $totalCreditAmount === false ? null : $this->trimLeftZeros($totalCreditAmount),
            'totalDebitAmount' => $totalDebitAmount === false ? null : $this->trimLeftZeros($totalDebitAmount),
            'totalNetAmount' => $totalNetAmount === false ? null : $this->trimLeftZeros($totalNetAmount),
            'totalRecordCount' => $totalRecordCount === false ? null : $this->trimLeftZeros($totalRecordCount),
        ]);
    }
}
