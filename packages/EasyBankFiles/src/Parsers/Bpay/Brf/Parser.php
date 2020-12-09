<?php

declare(strict_types=1);

namespace EonX\EasyBankFiles\Parsers\Bpay\Brf;

use EonX\EasyBankFiles\Parsers\AbstractLineByLineParser;
use EonX\EasyBankFiles\Parsers\Bpay\Brf\Results\Header;
use EonX\EasyBankFiles\Parsers\Bpay\Brf\Results\Trailer;
use EonX\EasyBankFiles\Parsers\Bpay\Brf\Results\Transaction;
use EonX\EasyBankFiles\Parsers\Error;

final class Parser extends AbstractLineByLineParser
{
    /**
     * @var string
     */
    private const HEADER = '00';

    /**
     * @var string
     */
    private const TRAILER = '99';

    /**
     * @var string
     */
    private const TRANSACTION = '50';

    /**
     * @var mixed[] $errors
     */
    protected $errors = [];

    /**
     * @var \EonX\EasyBankFiles\Parsers\Bpay\Brf\Results\Header
     */
    protected $header;

    /**
     * @var \EonX\EasyBankFiles\Parsers\Bpay\Brf\Results\Trailer
     */
    protected $trailer;

    /**
     * @var \EonX\EasyBankFiles\Parsers\Bpay\Brf\Results\Transaction[] $transactions
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
     * @return \EonX\EasyBankFiles\Parsers\Bpay\Brf\Results\Transaction[]
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
        $code = \substr($line, 0, 2);

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
        $billerCode = \substr($line, 2, 10);
        $billerShortName = \substr($line, 12, 20);
        $billerCreditBSB = \substr($line, 32, 6);
        $billerCreditAccount = \substr($line, 38, 9);
        $fileCreationDate = \substr($line, 47, 8);
        $fileCreationTime = \substr($line, 55, 6);
        $restOfRecord = \substr($line, 61, 158);

        return new Header([
            'billerCode' => $billerCode === false ? null : $this->trimLeftZeros($billerCode),
            'billerShortName' => $billerShortName === false ? null : \trim($billerShortName),
            'billerCreditBSB' => $billerCreditBSB === false ? null : $billerCreditBSB,
            'billerCreditAccount' => $billerCreditAccount === false ? null : $billerCreditAccount,
            'fileCreationDate' => $fileCreationDate === false ? null : $fileCreationDate,
            'fileCreationTime' => $fileCreationTime === false ? null : $fileCreationTime,
            'restOfRecord' => $restOfRecord === false ? null : $restOfRecord,
        ]);
    }

    /**
     * Parse trailer.
     */
    private function processTrailer(string $line): Trailer
    {
        $billerCode = \substr($line, 2, 10);
        $numberOfPayments = \substr($line, 12, 9);
        $amountOfPayments = \substr($line, 21, 15);
        $numberOfErrorCorrections = \substr($line, 36, 9);
        $amountOfErrorCorrections = \substr($line, 45, 15);
        $numberOfReversals = \substr($line, 60, 9);
        $amountOfReversals = \substr($line, 69, 15);
        $settlementAmount = \substr($line, 84, 15);
        $restOfRecord = \substr($line, 99, 120);

        return new Trailer([
            'billerCode' => $billerCode === false ? null : $this->trimLeftZeros($billerCode),
            'numberOfPayments' => $numberOfPayments === false ? null : $this->trimLeftZeros($numberOfPayments),
            'amountOfPayments' => $amountOfPayments === false ? null : $this->trimLeftZeros($amountOfPayments),
            'numberOfErrorCorrections' => $numberOfErrorCorrections === false ? null : $this->trimLeftZeros($numberOfErrorCorrections),
            'amountOfErrorCorrections' => $amountOfErrorCorrections === false ? null : $this->trimLeftZeros($amountOfErrorCorrections),
            'numberOfReversals' => $numberOfReversals === false ? null : $this->trimLeftZeros($numberOfReversals),
            'amountOfReversals' => $amountOfReversals === false ? null : $this->trimLeftZeros($amountOfReversals),
            'settlementAmount' => $settlementAmount === false ? null : $this->trimLeftZeros($settlementAmount),
            'restOfRecord' => $restOfRecord === false ? null : $restOfRecord,
        ]);
    }

    /**
     * Parse transaction items.
     */
    private function processTransaction(string $line): Transaction
    {
        $billerCode = \substr($line, 2, 10);
        $customerReferenceNumber = \substr($line, 12, 20);
        $paymentInstructionType = \substr($line, 32, 2);
        $transactionReferenceNumber = \substr($line, 34, 21);
        $originalReferenceNumber = \substr($line, 55, 21);
        $errorCorrectionReason = \substr($line, 76, 3);
        $amount = \substr($line, 79, 12);
        $paymentDate = \substr($line, 91, 8);
        $paymentTime = \substr($line, 99, 6);
        $settlementDate = \substr($line, 105, 8);
        $restOfRecord = \substr($line, 113, 106);

        return new Transaction([
            'billerCode' => $billerCode === false ? null : $this->trimLeftZeros($billerCode),
            'customerReferenceNumber' => $customerReferenceNumber === false ? null : \trim($customerReferenceNumber),
            'paymentInstructionType' => $paymentInstructionType === false ? null : $paymentInstructionType,
            'transactionReferenceNumber' => $transactionReferenceNumber === false ? null : \trim($transactionReferenceNumber),
            'originalReferenceNumber' => $originalReferenceNumber === false ? null : \trim($originalReferenceNumber),
            'errorCorrectionReason' => $errorCorrectionReason === false ? null : $errorCorrectionReason,
            'amount' => $amount === false ? null : $this->trimLeftZeros($amount),
            'paymentDate' => $paymentDate === false ? null : $paymentDate,
            'paymentTime' => $paymentTime === false ? null : $paymentTime,
            'settlementDate' => $settlementDate === false ? null : $settlementDate,
            'restOfRecord' => $restOfRecord === false ? null : $restOfRecord,
        ]);
    }
}
