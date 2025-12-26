<?php
declare(strict_types=1);

namespace EonX\EasyBankFiles\Parsing\Brf\Parser;

use EonX\EasyBankFiles\Parsing\Brf\ValueObject\DetailRecord;
use EonX\EasyBankFiles\Parsing\Brf\ValueObject\HeaderRecord;
use EonX\EasyBankFiles\Parsing\Brf\ValueObject\TrailerRecord;
use EonX\EasyBankFiles\Parsing\Common\Parser\AbstractLineByLineParser;
use EonX\EasyBankFiles\Parsing\Common\ValueObject\Error;

final class BrfParser extends AbstractLineByLineParser
{
    private const string RECORD_TYPE_DETAIL = '50';

    private const string RECORD_TYPE_HEADER = '00';

    private const string RECORD_TYPE_TRAILER = '99';

    /**
     * @var \EonX\EasyBankFiles\Parsing\Brf\ValueObject\DetailRecord[] $detailRecords
     */
    private array $detailRecords = [];

    /**
     * @var \EonX\EasyBankFiles\Parsing\Common\ValueObject\Error[] $errors
     */
    private array $errors = [];

    private HeaderRecord $headerRecord;

    private TrailerRecord $trailerRecord;

    /**
     * @return \EonX\EasyBankFiles\Parsing\Brf\ValueObject\DetailRecord[]
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
        $code = \substr($line, 0, 2);

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
     * Parse transaction items.
     */
    private function processDetailRecord(string $line): DetailRecord
    {
        /** @var string|false $billerCode */
        $billerCode = \substr($line, 2, 10);
        /** @var string|false $customerReferenceNumber */
        $customerReferenceNumber = \substr($line, 12, 20);
        /** @var string|false $paymentInstructionType */
        $paymentInstructionType = \substr($line, 32, 2);
        /** @var string|false $transactionReferenceNumber */
        $transactionReferenceNumber = \substr($line, 34, 21);
        /** @var string|false $originalReferenceNumber */
        $originalReferenceNumber = \substr($line, 55, 21);
        /** @var string|false $errorCorrectionReason */
        $errorCorrectionReason = \substr($line, 76, 3);
        /** @var string|false $amount */
        $amount = \substr($line, 79, 12);
        /** @var string|false $paymentDate */
        $paymentDate = \substr($line, 91, 8);
        /** @var string|false $paymentTime */
        $paymentTime = \substr($line, 99, 6);
        /** @var string|false $settlementDate */
        $settlementDate = \substr($line, 105, 8);
        /** @var string|false $filler */
        $filler = \substr($line, 113, 106);

        return new DetailRecord([
            'amount' => $amount === false ? null : $this->trimLeftZeros($amount),
            'billerCode' => $billerCode === false ? null : $this->trimLeftZeros($billerCode),
            'customerReferenceNumber' => $customerReferenceNumber === false ? null : \trim($customerReferenceNumber),
            'errorCorrectionReason' => $errorCorrectionReason === false ? null : $errorCorrectionReason,
            'filler' => $filler === false ? null : $filler,
            'originalReferenceNumber' => $originalReferenceNumber === false ? null : \trim($originalReferenceNumber),
            'paymentDate' => $paymentDate === false ? null : $paymentDate,
            'paymentInstructionType' => $paymentInstructionType === false ? null : $paymentInstructionType,
            'paymentTime' => $paymentTime === false ? null : $paymentTime,
            'settlementDate' => $settlementDate === false ? null : $settlementDate,
            'transactionReferenceNumber' => $transactionReferenceNumber === false
                ? null
                : \trim($transactionReferenceNumber),
        ]);
    }

    /**
     * Parse header.
     */
    private function processHeaderRecord(string $line): HeaderRecord
    {
        /** @var string|false $billerCode */
        $billerCode = \substr($line, 2, 10);
        /** @var string|false $billerShortName */
        $billerShortName = \substr($line, 12, 20);
        /** @var string|false $billerCreditBSB */
        $billerCreditBSB = \substr($line, 32, 6);
        /** @var string|false $billerCreditAccount */
        $billerCreditAccount = \substr($line, 38, 9);
        /** @var string|false $fileCreationDate */
        $fileCreationDate = \substr($line, 47, 8);
        /** @var string|false $fileCreationTime */
        $fileCreationTime = \substr($line, 55, 6);
        /** @var string|false $filler */
        $filler = \substr($line, 61, 158);

        return new HeaderRecord([
            'billerCode' => $billerCode === false ? null : $this->trimLeftZeros($billerCode),
            'billerCreditAccount' => $billerCreditAccount === false ? null : $billerCreditAccount,
            'billerCreditBSB' => $billerCreditBSB === false ? null : $billerCreditBSB,
            'billerShortName' => $billerShortName === false ? null : \trim($billerShortName),
            'fileCreationDate' => $fileCreationDate === false ? null : $fileCreationDate,
            'fileCreationTime' => $fileCreationTime === false ? null : $fileCreationTime,
            'filler' => $filler === false ? null : $filler,
        ]);
    }

    /**
     * Parse trailer.
     */
    private function processTrailerRecord(string $line): TrailerRecord
    {
        /** @var string|false $billerCode */
        $billerCode = \substr($line, 2, 10);
        /** @var string|false $numberOfPayments */
        $numberOfPayments = \substr($line, 12, 9);
        /** @var string|false $amountOfPayments */
        $amountOfPayments = \substr($line, 21, 15);
        /** @var string|false $numberOfErrorCorrections */
        $numberOfErrorCorrections = \substr($line, 36, 9);
        /** @var string|false $amountOfErrorCorrections */
        $amountOfErrorCorrections = \substr($line, 45, 15);
        /** @var string|false $numberOfReversals */
        $numberOfReversals = \substr($line, 60, 9);
        /** @var string|false $amountOfReversals */
        $amountOfReversals = \substr($line, 69, 15);
        /** @var string|false $settlementAmount */
        $settlementAmount = \substr($line, 84, 15);
        /** @var string|false $filler */
        $filler = \substr($line, 99, 120);

        return new TrailerRecord([
            'amountOfErrorCorrections' => $amountOfErrorCorrections === false
                ? null
                : $this->trimLeftZeros($amountOfErrorCorrections),
            'amountOfPayments' => $amountOfPayments === false ? null : $this->trimLeftZeros($amountOfPayments),
            'amountOfReversals' => $amountOfReversals === false ? null : $this->trimLeftZeros($amountOfReversals),
            'billerCode' => $billerCode === false ? null : $this->trimLeftZeros($billerCode),
            'filler' => $filler === false ? null : $filler,
            'numberOfErrorCorrections' => $numberOfErrorCorrections === false
                ? null
                : $this->trimLeftZeros($numberOfErrorCorrections),
            'numberOfPayments' => $numberOfPayments === false ? null : $this->trimLeftZeros($numberOfPayments),
            'numberOfReversals' => $numberOfReversals === false ? null : $this->trimLeftZeros($numberOfReversals),
            'settlementAmount' => $settlementAmount === false ? null : $this->trimLeftZeros($settlementAmount),
        ]);
    }
}
