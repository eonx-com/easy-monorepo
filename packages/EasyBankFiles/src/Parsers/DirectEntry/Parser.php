<?php

declare(strict_types=1);

namespace EonX\EasyBankFiles\Parsers\DirectEntry;

use EonX\EasyBankFiles\Parsers\AbstractLineByLineParser;
use EonX\EasyBankFiles\Parsers\DirectEntry\Results\Batch;
use EonX\EasyBankFiles\Parsers\DirectEntry\Results\Batch\Header;
use EonX\EasyBankFiles\Parsers\DirectEntry\Results\Batch\Trailer;
use EonX\EasyBankFiles\Parsers\DirectEntry\Results\Batch\TransactionType1;
use EonX\EasyBankFiles\Parsers\DirectEntry\Results\Batch\TransactionType2;
use EonX\EasyBankFiles\Parsers\DirectEntry\Results\Batch\TransactionType3;
use EonX\EasyBankFiles\Parsers\Error;

/**
 * @SuppressWarnings(PHPMD.ExcessiveClassComplexity) We can't reduce the overall complexity
 */
final class Parser extends AbstractLineByLineParser
{
    private const FINAL_LINE = 'End-Of-File';

    private const MIN_RECORD_LENGTH = [
        self::RECORD_TYPE_HEADER => 80,
        self::RECORD_TYPE_TRAILER => 80,
        self::RECORD_TYPE_TRANSACTION_1 => 120,
        self::RECORD_TYPE_TRANSACTION_2 => 120,
        self::RECORD_TYPE_TRANSACTION_3 => 120,
    ];

    private const RECORD_TYPE_HEADER = '0';

    private const RECORD_TYPE_TRAILER = '7';

    private const RECORD_TYPE_TRANSACTION_1 = '1';

    private const RECORD_TYPE_TRANSACTION_2 = '2';

    private const RECORD_TYPE_TRANSACTION_3 = '3';

    /**
     * @var \App\Infrastructure\EasyBankFiles\Parser\Nde\Result\Batch[]
     */
    private array $batches = [];

    private ?Batch $currentBatch = null;

    /**
     * @var mixed[] $errors
     */
    private array $errors = [];

    /**
     * @return \App\Infrastructure\EasyBankFiles\Parser\Nde\Result\Batch[]
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
        $contents = \preg_split("/[\r\n]/", $this->contents);
        $lineNumber = 1;

        foreach ($contents as $line) {
            $line = \trim($line);

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
                self::RECORD_TYPE_HEADER => $this->processHeader($line),
                self::RECORD_TYPE_TRAILER => $this->processTrailer($line),
                self::RECORD_TYPE_TRANSACTION_1 => $this->processTransactionType1($line),
                self::RECORD_TYPE_TRANSACTION_2 => $this->processTransactionType2($line),
                self::RECORD_TYPE_TRANSACTION_3 => $this->processTransactionType3($line),
                default => $this->isFinalLine($line)
            };

            if ($lineProcessingResult === true) {
                return;
            }
        }

        $this->errors[] = new Error(\compact('line', 'lineNumber'));
    }

    private function addTransactionToCurrentBatch(TransactionType1|TransactionType2|TransactionType3 $transaction): bool
    {
        if (isset($this->currentBatch) === false || $this->currentBatch->hasTrailer()) {
            $this->currentBatch = null;

            return false;
        }
        $this->currentBatch->addTransaction($transaction);

        return true;
    }

    private function checkMinLength(string $line, string $code): bool
    {
        return isset(self::MIN_RECORD_LENGTH[$code]) === false || \strlen($line) >= self::MIN_RECORD_LENGTH[$code];
    }

    private function isFinalLine(string $line): bool
    {
        return (\trim($line) === self::FINAL_LINE || \trim($line, '9') === '') &&
            ($this->currentBatch === null || $this->currentBatch->hasTrailer());
    }

    /**
     * @return array<mixed>
     *
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     */
    private function parseCommonTransactionAttributes(string $line): array
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
        /** @var string|false $remitterName */
        $remitterName = \substr($line, 96, 16);
        /** @var string|false $traceAccountNumber */
        $traceAccountNumber = \substr($line, 87, 9);
        /** @var string|false $traceBsb */
        $traceBsb = \substr($line, 80, 7);
        /** @var string|false $txnCode */
        $txnCode = \substr($line, 18, 2);

        return [
            'accountName' => $accountName === false ? null : \trim($accountName),
            'accountNumber' => $accountNumber === false ? null : $accountNumber,
            'amount' => $amount === false ? null : $this->trimLeftZeros($amount),
            'bsb' => $bsb === false ? null : \str_replace('-', '', $bsb),
            'indicator' => $line[17] ?? '',
            'lodgmentReference' => $lodgmentReference === false ? null : \trim($lodgmentReference),
            'recordType' => $line[0] ?? '',
            'remitterName' => $remitterName === false ? null : \trim($remitterName),
            'traceAccountNumber' => $traceAccountNumber === false ? null : $traceAccountNumber,
            'traceBsb' => $traceBsb === false ? null : \str_replace('-', '', $traceBsb),
            'txnCode' => $txnCode === false ? null : $txnCode,
        ];
    }

    private function processHeader(string $line): bool
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

        return $this->setHeaderToCurrentBatch(new Header([
            'dateProcessed' => $dateProcessed === false ? null : $dateProcessed,
            'description' => $description === false ? null : \trim($description),
            'reelSequenceNumber' => $reelSequenceNumber === false ? null : $reelSequenceNumber,
            'userFinancialInstitution' => $userFinancialInstitution === false ? null : $userFinancialInstitution,
            'userIdSupplyingFile' => $userIdSupplyingFile === false ? null : $userIdSupplyingFile,
            'userSupplyingFile' => $userSupplyingFile === false ? null : \trim($userSupplyingFile),
        ]));
    }

    private function processTrailer(string $line): bool
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

        return $this->setTrailerToCurrentBatch(new Trailer([
            'bsb' => $bsb === false ? null : \str_replace('-', '', $bsb),
            'numberPayments' => $numberPayments === false ? null : $this->trimLeftZeros($numberPayments),
            'totalCreditAmount' => $totalCreditAmount === false ? null : $this->trimLeftZeros($totalCreditAmount),
            'totalDebitAmount' => $totalDebitAmount === false ? null : $this->trimLeftZeros($totalDebitAmount),
            'totalNetAmount' => $totalNetAmount === false ? null : $this->trimLeftZeros($totalNetAmount),
        ]));
    }

    private function processTransactionType1(string $line): bool
    {
        /** @var string|false $withholdingTax */
        $withholdingTax = \substr($line, 112, 8);

        return $this->addTransactionToCurrentBatch(new TransactionType1(\array_merge(
            [
                'withholdingTax' => $withholdingTax === false ? null : $this->trimLeftZeros($withholdingTax),
            ],
            $this->parseCommonTransactionAttributes($line)
        )));
    }

    private function processTransactionType2(string $line): bool
    {
        /** @var string|false $originalDayOfProcessing */
        $originalDayOfProcessing = \substr($line, 112, 2);
        /** @var string|false $originalUserIdNumber */
        $originalUserIdNumber = \substr($line, 114, 6);

        return $this->addTransactionToCurrentBatch(new TransactionType2(\array_merge(
            [
                'originalDayOfProcessing' => $originalDayOfProcessing === false ? null : $originalDayOfProcessing,
                'originalUserIdNumber' => $originalUserIdNumber === false ? null : \trim($originalUserIdNumber),
            ],
            $this->parseCommonTransactionAttributes($line)
        )));
    }

    private function processTransactionType3(string $line): bool
    {
        /** @var string|false $originalDayOfReturn */
        $originalDayOfReturn = \substr($line, 112, 2);
        /** @var string|false $originalUserIdNumber */
        $originalUserIdNumber = \substr($line, 114, 6);

        return $this->addTransactionToCurrentBatch(new TransactionType3(\array_merge(
            [
                'originalDayOfReturn' => $originalDayOfReturn === false ? null : $originalDayOfReturn,
                'originalUserIdNumber' => $originalUserIdNumber === false ? null : \trim($originalUserIdNumber),
            ],
            $this->parseCommonTransactionAttributes($line)
        )));
    }

    private function setHeaderToCurrentBatch(Header $header): bool
    {
        $recordProcessed = $this->currentBatch === null;
        $this->currentBatch = (new Batch())->setHeader($header);

        return $recordProcessed;
    }

    private function setTrailerToCurrentBatch(Trailer $trailer): bool
    {
        if (isset($this->currentBatch) === false || $this->currentBatch->hasTransaction() === false) {
            $this->currentBatch = null;

            return false;
        }
        $this->batches[] = $this->currentBatch->setTrailer($trailer);
        $this->currentBatch = null;

        return true;
    }
}
