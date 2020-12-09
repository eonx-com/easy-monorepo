<?php

declare(strict_types=1);

namespace EonX\EasyBankFiles\Parsers\Bpay\Batch;

use EonX\EasyBankFiles\Parsers\AbstractLineByLineParser;
use EonX\EasyBankFiles\Parsers\Bpay\Batch\Results\Header;
use EonX\EasyBankFiles\Parsers\Bpay\Batch\Results\Trailer;
use EonX\EasyBankFiles\Parsers\Bpay\Batch\Results\Transaction;
use EonX\EasyBankFiles\Parsers\Error;

final class Parser extends AbstractLineByLineParser
{
    /**
     * @var string
     */
    private const HEADER = '1';

    /**
     * @var string
     */
    private const TRAILER = '9';

    /**
     * @var string
     */
    private const TRANSACTION = '2';

    /**
     * @var \EonX\EasyBankFiles\Parsers\Error[]
     */
    protected $errors = [];

    /**
     * @var \EonX\EasyBankFiles\Parsers\Bpay\Batch\Results\Header
     */
    protected $header;

    /**
     * @var \EonX\EasyBankFiles\Parsers\Bpay\Batch\Results\Trailer
     */
    protected $trailer;

    /**
     * @var \EonX\EasyBankFiles\Parsers\Bpay\Batch\Results\Transaction[]
     */
    protected $transactions;

    /**
     * @return \EonX\EasyBankFiles\Parsers\Error[]
     */
    public function getErrors(): array
    {
        return $this->errors;
    }

    /**
     * Return the Header object.
     */
    public function getHeader(): Header
    {
        return $this->header;
    }

    /**
     * Return the Trailer object.
     */
    public function getTrailer(): Trailer
    {
        return $this->trailer;
    }

    /**
     * @return \EonX\EasyBankFiles\Parsers\Bpay\Batch\Results\Transaction[]
     */
    public function getTransactions(): array
    {
        return $this->transactions;
    }

    /**
     * Process line and parse data.
     */
    protected function processLine(int $lineNumber, string $line): void
    {
        $code = $line[0];

        switch ($code) {
            case self::HEADER:
                $this->header = $this->processHeader($line);
                break;

            case self::TRANSACTION:
                $this->transactions[] = $this->processTransaction($line);
                break;

            case self::TRAILER:
                $this->trailer = $this->processTrailer($line);
                break;

            default:
                $this->errors[] = new Error(\compact('line', 'lineNumber'));
                break;
        }
    }

    /**
     * Parse header.
     */
    private function processHeader(string $line): Header
    {
        $customerId = \substr($line, 1, 16);
        $customerShortName = \substr($line, 17, 20);
        $dateProcessed = \substr($line, 37, 8);
        $restOfRecord = \substr($line, 45, 174);

        return new Header([
            'customerId' => $customerId === false ? null : \trim($customerId),
            'customerShortName' => $customerShortName === false ? null : \trim($customerShortName),
            'dateProcessed' => $dateProcessed === false ? null : $dateProcessed,
            'restOfRecord' => $restOfRecord === false ? null : $restOfRecord,
        ]);
    }

    /**
     * Parse trailer.
     */
    private function processTrailer(string $line): Trailer
    {
        $numberOfApprovals = \substr($line, 1, 10);
        $amountOfApprovals = \substr($line, 11, 13);
        $numberOfDeclines = \substr($line, 24, 10);
        $amountOfDeclines = \substr($line, 34, 13);
        $numberOfPayments = \substr($line, 47, 10);
        $amountOfPayments = \substr($line, 57, 13);
        $restOfRecord = \substr($line, 70, 149);

        return new Trailer([
            'numberOfApprovals' => $numberOfApprovals === false ? null : $this->trimLeftZeros($numberOfApprovals),
            'amountOfApprovals' => $amountOfApprovals === false ? null : $this->trimLeftZeros($amountOfApprovals),
            'numberOfDeclines' => $numberOfDeclines === false ? null : $this->trimLeftZeros($numberOfDeclines),
            'amountOfDeclines' => $amountOfDeclines === false ? null : $this->trimLeftZeros($amountOfDeclines),
            'numberOfPayments' => $numberOfPayments === false ? null : $this->trimLeftZeros($numberOfPayments),
            'amountOfPayments' => $amountOfPayments === false ? null : $this->trimLeftZeros($amountOfPayments),
            'restOfRecord' => $restOfRecord === false ? null : $restOfRecord,
        ]);
    }

    /**
     * Parse transaction items.
     */
    private function processTransaction(string $line): Transaction
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

        return new Transaction([
            'billerCode' => $billerCode === false ? null : $this->trimLeftZeros($billerCode),
            'accountBsb' => $accountBsb === false ? null : $accountBsb,
            'accountNumber' => $accountNumber === false ? null : $accountNumber,
            'customerReferenceNumber' => $customerReferenceNumber === false ? null : \trim($customerReferenceNumber),
            'amount' => $amount === false ? null : $this->trimLeftZeros($amount),
            'reference1' => $reference1 === false ? null : \trim($reference1),
            'reference2' => $reference2 === false ? null : \trim($reference2),
            'reference3' => $reference3 === false ? null : \trim($reference3),
            'returnCode' => $returnCode === false ? null : $returnCode,
            'returnCodeDescription' => $returnCodeDescription === false ? null : \trim($returnCodeDescription),
            'transactionReferenceNumber' => $transactionReferenceNumber === false ? null : $transactionReferenceNumber,
            'restOfRecord' => $restOfRecord === false ? null : $restOfRecord,
        ]);
    }
}
