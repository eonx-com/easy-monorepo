<?php

declare(strict_types=1);

namespace EonX\EasyBankFiles\Parsers\Bpay\Brf;

use EonX\EasyBankFiles\Parsers\AbstractLineByLineParser;
use EonX\EasyBankFiles\Parsers\Bpay\Brf\Results\Error;
use EonX\EasyBankFiles\Parsers\Bpay\Brf\Results\Header;
use EonX\EasyBankFiles\Parsers\Bpay\Brf\Results\Trailer;
use EonX\EasyBankFiles\Parsers\Bpay\Brf\Results\Transaction;

final class Parser extends AbstractLineByLineParser
{
    /**
     * @const string
     */
    private const HEADER = '00';

    /**
     * @const string
     */
    private const TRAILER = '99';

    /**
     * @const string
     */
    private const TRANSACTION = '50';

    /**
     * @var mixed[] $errors
     */
    protected $errors;

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
     * @return \EonX\EasyBankFiles\Parsers\Bpay\Brf\Results\Error[]
     */
    public function getErrors(): array
    {
        return $this->errors;
    }

    /**
     * Return the Header object
     */
    public function getHeader(): Header
    {
        return $this->header;
    }

    /**
     * Return the Trailer object
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
     * Process line and parse data
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
     * Parse header
     */
    private function processHeader(string $line): Header
    {
        return new Header([
            'billerCode' => \substr($line, 2, 10),
            'billerShortName' => \substr($line, 12, 20),
            'billerCreditBSB' => \substr($line, 32, 6),
            'billerCreditAccount' => \substr($line, 38, 9),
            'fileCreationDate' => \substr($line, 47, 8),
            'fileCreationTime' => \substr($line, 55, 6),
            'filler' => \substr($line, 61, 158),
        ]);
    }

    /**
     * Parse trailer
     */
    private function processTrailer(string $line): Trailer
    {
        return new Trailer([
            'billerCode' => \substr($line, 2, 10),
            'numberOfPayments' => \substr($line, 12, 9),
            'amountOfPayments' => \substr($line, 21, 15),
            'numberOfErrorCorrections' => \substr($line, 36, 9),
            'amountOfErrorCorrections' => \substr($line, 45, 15),
            'numberOfReversals' => \substr($line, 60, 9),
            'amountOfReversals' => \substr($line, 69, 15),
            'settlementAmount' => \substr($line, 84, 15),
            'filler' => \substr($line, 99, 120),
        ]);
    }

    /**
     * Parse transaction items
     */
    private function processTransaction(string $line): Transaction
    {
        return new Transaction([
            'billerCode' => \substr($line, 2, 10),
            'customerReferenceNumber' => \substr($line, 12, 20),
            'paymentInstructionType' => \substr($line, 32, 2),
            'transactionReferenceNumber' => \substr($line, 34, 21),
            'originalReferenceNumber' => \substr($line, 55, 21),
            'errorCorrectionReason' => \substr($line, 76, 3),
            'amount' => \substr($line, 79, 12),
            'paymentDate' => \substr($line, 91, 8),
            'paymentTime' => \substr($line, 99, 6),
            'settlementDate' => \substr($line, 105, 8),
            'filler' => \substr($line, 113, 106),
        ]);
    }
}
