<?php
declare(strict_types=1);

namespace EonX\EasyBankFiles\Parsing\DirectEntryReturn\Parser;

use EonX\EasyBankFiles\Parsing\Common\Parser\AbstractLineByLineParser;
use EonX\EasyBankFiles\Parsing\Common\ValueObject\Error;
use EonX\EasyBankFiles\Parsing\DirectEntryReturn\ValueObject\Header;
use EonX\EasyBankFiles\Parsing\DirectEntryReturn\ValueObject\Trailer;
use EonX\EasyBankFiles\Parsing\DirectEntryReturn\ValueObject\Transaction;

final class DirectEntryReturnParser extends AbstractLineByLineParser
{
    private const HEADER = '0';

    private const MIN_HEADER_LINE_LENGTH = 80;

    private const MIN_TRAILER_LINE_LENGTH = 80;

    private const MIN_TRANSACTION_LINE_LENGTH = 120;

    private const TRAILER = '7';

    private const TRANSACTION_1 = '1';

    private const TRANSACTION_2 = '2';

    /**
     * @var \EonX\EasyBankFiles\Parsing\Common\ValueObject\Error[] $errors
     */
    private array $errors = [];

    private Header $header;

    private Trailer $trailer;

    /**
     * @var \EonX\EasyBankFiles\Parsing\DirectEntryReturn\ValueObject\Transaction[]
     */
    private array $transactions = [];

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
    public function getHeader(): Header
    {
        return $this->header;
    }

    /**
     * Get trailer record.
     */
    public function getTrailer(): Trailer
    {
        return $this->trailer;
    }

    /**
     * @return \EonX\EasyBankFiles\Parsing\DirectEntryReturn\ValueObject\Transaction[]
     */
    public function getTransactions(): array
    {
        return $this->transactions;
    }

    protected function processLine(int $lineNumber, string $line): void
    {
        // Code is the first character in line
        $code = $line[0] ?? self::EMPTY_LINE_CODE;
        $lineLength = \strlen($line);

        if ($code === self::HEADER && $lineLength >= self::MIN_HEADER_LINE_LENGTH) {
            $this->header = $this->processHeader($line);

            return;
        }

        if ($code === self::TRAILER && $lineLength >= self::MIN_TRAILER_LINE_LENGTH) {
            $this->trailer = $this->processTrailer($line);

            return;
        }

        if (
            ($code === self::TRANSACTION_1 || $code === self::TRANSACTION_2) &&
            $lineLength >= self::MIN_TRANSACTION_LINE_LENGTH
        ) {
            $this->transactions[] = $this->processTransaction($line);

            return;
        }

        $this->errors[] = new Error(\compact('line', 'lineNumber'));
    }

    /**
     * Process header block of line.
     */
    private function processHeader(string $line): Header
    {
        /** @var string|false $dateProcessed */
        $dateProcessed = \substr($line, 74, 6);
        /** @var string|false $description */
        $description = \substr($line, 62, 12);
        /** @var string|false $userFinancialInstitution */
        $userFinancialInstitution = \substr($line, 20, 3);
        /** @var string|false $userIdSupplyingFile */
        $userIdSupplyingFile = \substr($line, 56, 6);
        /** @var string|false $userSupplyingFile */
        $userSupplyingFile = \substr($line, 30, 26);
        /** @var string|false $reelSequenceNumber */
        $reelSequenceNumber = \substr($line, 18, 2);

        return new Header([
            'dateProcessed' => $dateProcessed === false ? null : $dateProcessed,
            'description' => $description === false ? null : \trim($description),
            'reelSequenceNumber' => $reelSequenceNumber === false ? null : $reelSequenceNumber,
            'userFinancialInstitution' => $userFinancialInstitution === false ? null : $userFinancialInstitution,
            'userIdSupplyingFile' => $userIdSupplyingFile === false ? null : $userIdSupplyingFile,
            'userSupplyingFile' => $userSupplyingFile === false ? null : \trim($userSupplyingFile),
        ]);
    }

    /**
     * Process trailer block of line.
     */
    private function processTrailer(string $line): Trailer
    {
        /** @var string|false $bsb */
        $bsb = \substr($line, 1, 7);
        /** @var string|false $numberPayments */
        $numberPayments = \substr($line, 74, 6);
        /** @var string|false $totalNetAmount */
        $totalNetAmount = \substr($line, 20, 10);
        /** @var string|false $totalCreditAmount */
        $totalCreditAmount = \substr($line, 30, 10);
        /** @var string|false $totalDebitAmount */
        $totalDebitAmount = \substr($line, 40, 10);

        return new Trailer([
            'bsb' => $bsb === false ? null : \str_replace('-', '', $bsb),
            'numberPayments' => $numberPayments === false ? null : $this->trimLeftZeros($numberPayments),
            'totalCreditAmount' => $totalCreditAmount === false ? null : $this->trimLeftZeros($totalCreditAmount),
            'totalDebitAmount' => $totalDebitAmount === false ? null : $this->trimLeftZeros($totalDebitAmount),
            'totalNetAmount' => $totalNetAmount === false ? null : $this->trimLeftZeros($totalNetAmount),
        ]);
    }

    /**
     * Process transaction block of line.
     */
    private function processTransaction(string $line): Transaction
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
        /** @var string|false $txnCode */
        $txnCode = \substr($line, 18, 2);

        return new Transaction([
            'accountName' => $accountName === false ? null : \trim($accountName),
            'accountNumber' => $accountNumber === false ? null : $accountNumber,
            'amount' => $amount === false ? null : $this->trimLeftZeros($amount),
            'bsb' => $bsb === false ? null : \str_replace('-', '', $bsb),
            'indicator' => $line[17] ?? '',
            'lodgmentReference' => $lodgmentReference === false ? null : \trim($lodgmentReference),
            'originalDayOfProcessing' => $originalDayOfProcessing === false ? null : \trim($originalDayOfProcessing),
            'originalUserIdNumber' => $originalUserIdNumber === false ? null : \trim($originalUserIdNumber),
            'recordType' => $line[0] ?? '',
            'remitterName' => $remitterName === false ? null : \trim($remitterName),
            'traceAccountNumber' => $traceAccountNumber === false ? null : $traceAccountNumber,
            'traceBsb' => $traceBsb === false ? null : \str_replace('-', '', $traceBsb),
            'txnCode' => $txnCode === false ? null : $txnCode,
        ]);
    }
}
