<?php
declare(strict_types=1);

namespace EonX\EasyBankFiles\Parsing\BpayBatch\Parser;

use EonX\EasyBankFiles\Parsing\BpayBatch\ValueObject\HeaderRecord;
use EonX\EasyBankFiles\Parsing\BpayBatch\ValueObject\TrailerRecord;
use EonX\EasyBankFiles\Parsing\BpayBatch\ValueObject\DetailRecord;
use EonX\EasyBankFiles\Parsing\Common\Parser\AbstractLineByLineParser;
use EonX\EasyBankFiles\Parsing\Common\ValueObject\Error;

final class BpayBatchParser extends AbstractLineByLineParser
{
    private const RECORD_TYPE_HEADER = '1';

    private const RECORD_TYPE_TRAILER = '9';

    private const RECORD_TYPE_DETAIL = '2';

    /**
     * @var \EonX\EasyBankFiles\Parsing\Common\ValueObject\Error[]
     */
    private array $errors = [];

    private HeaderRecord $headerRecord;

    private TrailerRecord $trailerRecord;

    /**
     * @var \EonX\EasyBankFiles\Parsing\BpayBatch\ValueObject\DetailRecord[]
     */
    private array $detailRecords = [];

    /**
     * @return \EonX\EasyBankFiles\Parsing\Common\ValueObject\Error[]
     */
    public function getErrors(): array
    {
        return $this->errors;
    }

    /**
     * Return the Header object.
     */
    public function getHeaderRecord(): HeaderRecord
    {
        return $this->headerRecord;
    }

    /**
     * Return the Trailer object.
     */
    public function getTrailerRecord(): TrailerRecord
    {
        return $this->trailerRecord;
    }

    /**
     * @return \EonX\EasyBankFiles\Parsing\BpayBatch\ValueObject\DetailRecord[]
     */
    public function getDetailRecords(): array
    {
        return $this->detailRecords;
    }

    /**
     * Process line and parse data.
     */
    protected function processLine(int $lineNumber, string $line): void
    {
        $code = $line[0] ?? self::EMPTY_LINE_CODE;

        switch ($code) {
            case self::RECORD_TYPE_HEADER:
                $this->headerRecord = $this->processHeaderRecord($line);

                break;

            case self::RECORD_TYPE_DETAIL:
                $this->detailRecords[] = $this->processDetailRecord($line);

                break;

            case self::RECORD_TYPE_TRAILER:
                $this->trailerRecord = $this->processTrailerRecord($line);

                break;

            default:
                $this->errors[] = new Error(\compact('line', 'lineNumber'));

                break;
        }
    }

    /**
     * Parse header.
     */
    private function processHeaderRecord(string $line): HeaderRecord
    {
        /** @var string|false $customerId */
        $customerId = \substr($line, 1, 16);
        /** @var string|false $customerShortName */
        $customerShortName = \substr($line, 17, 20);
        /** @var string|false $dateProcessed */
        $dateProcessed = \substr($line, 37, 8);
        /** @var string|false $restOfRecord */
        $restOfRecord = \substr($line, 45, 174);

        return new HeaderRecord([
            'customerId' => $customerId === false ? null : \trim($customerId),
            'customerShortName' => $customerShortName === false ? null : \trim($customerShortName),
            'dateProcessed' => $dateProcessed === false ? null : $dateProcessed,
            'restOfRecord' => $restOfRecord === false ? null : $restOfRecord,
        ]);
    }

    /**
     * Parse trailer.
     */
    private function processTrailerRecord(string $line): TrailerRecord
    {
        /** @var string|false $numberOfApprovals */
        $numberOfApprovals = \substr($line, 1, 10);
        /** @var string|false $amountOfApprovals */
        $amountOfApprovals = \substr($line, 11, 13);
        /** @var string|false $numberOfDeclines */
        $numberOfDeclines = \substr($line, 24, 10);
        /** @var string|false $amountOfDeclines */
        $amountOfDeclines = \substr($line, 34, 13);
        /** @var string|false $numberOfPayments */
        $numberOfPayments = \substr($line, 47, 10);
        /** @var string|false $amountOfPayments */
        $amountOfPayments = \substr($line, 57, 13);
        /** @var string|false $restOfRecord */
        $restOfRecord = \substr($line, 70, 149);

        return new TrailerRecord([
            'amountOfApprovals' => $amountOfApprovals === false ? null : $this->trimLeftZeros($amountOfApprovals),
            'amountOfDeclines' => $amountOfDeclines === false ? null : $this->trimLeftZeros($amountOfDeclines),
            'amountOfPayments' => $amountOfPayments === false ? null : $this->trimLeftZeros($amountOfPayments),
            'numberOfApprovals' => $numberOfApprovals === false ? null : $this->trimLeftZeros($numberOfApprovals),
            'numberOfDeclines' => $numberOfDeclines === false ? null : $this->trimLeftZeros($numberOfDeclines),
            'numberOfPayments' => $numberOfPayments === false ? null : $this->trimLeftZeros($numberOfPayments),
            'restOfRecord' => $restOfRecord === false ? null : $restOfRecord,
        ]);
    }

    /**
     * Parse transaction items.
     */
    private function processDetailRecord(string $line): DetailRecord
    {
        /** @var string|false $billerCode */
        $billerCode = \substr($line, 1, 10);
        /** @var string|false $accountBsb */
        $accountBsb = \substr($line, 11, 6);
        /** @var string|false $accountNumber */
        $accountNumber = \substr($line, 17, 9);
        /** @var string|false $customerReferenceNumber */
        $customerReferenceNumber = \substr($line, 26, 20);
        /** @var string|false $amount */
        $amount = \substr($line, 46, 13);
        /** @var string|false $reference1 */
        $reference1 = \substr($line, 59, 10);
        /** @var string|false $reference2 */
        $reference2 = \substr($line, 69, 20);
        /** @var string|false $reference3 */
        $reference3 = \substr($line, 89, 50);
        /** @var string|false $returnCode */
        $returnCode = \substr($line, 139, 4);
        /** @var string|false $returnCodeDescription */
        $returnCodeDescription = \substr($line, 143, 50);
        /** @var string|false $transactionReferenceNumber */
        $transactionReferenceNumber = \substr($line, 193, 21);
        /** @var string|false $restOfRecord */
        $restOfRecord = \substr($line, 214, 5);

        return new DetailRecord([
            'accountBsb' => $accountBsb === false ? null : $accountBsb,
            'accountNumber' => $accountNumber === false ? null : $accountNumber,
            'amount' => $amount === false ? null : $this->trimLeftZeros($amount),
            'billerCode' => $billerCode === false ? null : $this->trimLeftZeros($billerCode),
            'customerReferenceNumber' => $customerReferenceNumber === false ? null : \trim($customerReferenceNumber),
            'reference1' => $reference1 === false ? null : \trim($reference1),
            'reference2' => $reference2 === false ? null : \trim($reference2),
            'reference3' => $reference3 === false ? null : \trim($reference3),
            'restOfRecord' => $restOfRecord === false ? null : $restOfRecord,
            'returnCode' => $returnCode === false ? null : $returnCode,
            'returnCodeDescription' => $returnCodeDescription === false ? null : \trim($returnCodeDescription),
            'transactionReferenceNumber' => $transactionReferenceNumber === false ? null : $transactionReferenceNumber,
        ]);
    }
}
