<?php
declare(strict_types=1);

namespace EonX\EasyBankFiles\Parsing\BpayBatch\Parser;

use EonX\EasyBankFiles\Parsing\BpayBatch\ValueObject\DetailRecord;
use EonX\EasyBankFiles\Parsing\BpayBatch\ValueObject\HeaderRecord;
use EonX\EasyBankFiles\Parsing\BpayBatch\ValueObject\TrailerRecord;
use EonX\EasyBankFiles\Parsing\Common\Parser\AbstractLineByLineParser;
use EonX\EasyBankFiles\Parsing\Common\ValueObject\Error;

final class BpayBatchParser extends AbstractLineByLineParser
{
    private const string RECORD_TYPE_DETAIL = '2';

    private const string RECORD_TYPE_HEADER = '1';

    private const string RECORD_TYPE_TRAILER = '9';

    /**
     * @var \EonX\EasyBankFiles\Parsing\BpayBatch\ValueObject\DetailRecord[]
     */
    private array $detailRecords = [];

    /**
     * @var \EonX\EasyBankFiles\Parsing\Common\ValueObject\Error[]
     */
    private array $errors = [];

    private HeaderRecord $headerRecord;

    private TrailerRecord $trailerRecord;

    /**
     * @return \EonX\EasyBankFiles\Parsing\BpayBatch\ValueObject\DetailRecord[]
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
     * Parse Detail Record items.
     */
    private function processDetailRecord(string $line): DetailRecord
    {
        $billerCode = \substr($line, 1, 10);
        $accountBsb = \substr($line, 11, 6);
        $accountNumber = \substr($line, 17, 9);
        $customerReferenceNumber = \substr($line, 26, 20);
        $amount = \substr($line, 46, 13);
        $reference1 = \substr($line, 59, 10);
        $reference2 = \substr($line, 69, 20);
        $reference3 = \substr($line, 89, 50);
        $returnCode = \substr($line, 139, 4);
        $returnCodeDescription = \substr($line, 143, 50);
        $transactionReferenceNumber = \substr($line, 193, 21);
        $restOfRecord = \substr($line, 214, 5);

        return new DetailRecord([
            'accountBsb' => $accountBsb,
            'accountNumber' => $accountNumber,
            'amount' => $this->trimLeftZeros($amount),
            'billerCode' => $this->trimLeftZeros($billerCode),
            'customerReferenceNumber' => \trim($customerReferenceNumber),
            'reference1' => \trim($reference1),
            'reference2' => \trim($reference2),
            'reference3' => \trim($reference3),
            'restOfRecord' => $restOfRecord,
            'returnCode' => $returnCode,
            'returnCodeDescription' => \trim($returnCodeDescription),
            'transactionReferenceNumber' => $transactionReferenceNumber,
        ]);
    }

    /**
     * Parse Header record.
     */
    private function processHeaderRecord(string $line): HeaderRecord
    {
        $customerId = \substr($line, 1, 16);
        $customerShortName = \substr($line, 17, 20);
        $dateProcessed = \substr($line, 37, 8);
        $restOfRecord = \substr($line, 45, 174);

        return new HeaderRecord([
            'customerId' => \trim($customerId),
            'customerShortName' => \trim($customerShortName),
            'dateProcessed' => $dateProcessed,
            'restOfRecord' => $restOfRecord,
        ]);
    }

    /**
     * Parse Trailer record.
     */
    private function processTrailerRecord(string $line): TrailerRecord
    {
        $numberOfApprovals = \substr($line, 1, 10);
        $amountOfApprovals = \substr($line, 11, 13);
        $numberOfDeclines = \substr($line, 24, 10);
        $amountOfDeclines = \substr($line, 34, 13);
        $numberOfPayments = \substr($line, 47, 10);
        $amountOfPayments = \substr($line, 57, 13);
        $restOfRecord = \substr($line, 70, 149);

        return new TrailerRecord([
            'amountOfApprovals' => $this->trimLeftZeros($amountOfApprovals),
            'amountOfDeclines' => $this->trimLeftZeros($amountOfDeclines),
            'amountOfPayments' => $this->trimLeftZeros($amountOfPayments),
            'numberOfApprovals' => $this->trimLeftZeros($numberOfApprovals),
            'numberOfDeclines' => $this->trimLeftZeros($numberOfDeclines),
            'numberOfPayments' => $this->trimLeftZeros($numberOfPayments),
            'restOfRecord' => $restOfRecord,
        ]);
    }
}
