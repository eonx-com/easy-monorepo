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
        $filler = \substr($line, 113, 106);

        return new DetailRecord([
            'amount' => $this->trimLeftZeros($amount),
            'billerCode' => $this->trimLeftZeros($billerCode),
            'customerReferenceNumber' => \trim($customerReferenceNumber),
            'errorCorrectionReason' => $errorCorrectionReason,
            'filler' => $filler,
            'originalReferenceNumber' => \trim($originalReferenceNumber),
            'paymentDate' => $paymentDate,
            'paymentInstructionType' => $paymentInstructionType,
            'paymentTime' => $paymentTime,
            'settlementDate' => $settlementDate,
            'transactionReferenceNumber' => \trim($transactionReferenceNumber),
        ]);
    }

    /**
     * Parse header.
     */
    private function processHeaderRecord(string $line): HeaderRecord
    {
        $billerCode = \substr($line, 2, 10);
        $billerShortName = \substr($line, 12, 20);
        $billerCreditBSB = \substr($line, 32, 6);
        $billerCreditAccount = \substr($line, 38, 9);
        $fileCreationDate = \substr($line, 47, 8);
        $fileCreationTime = \substr($line, 55, 6);
        $filler = \substr($line, 61, 158);

        return new HeaderRecord([
            'billerCode' => $this->trimLeftZeros($billerCode),
            'billerCreditAccount' => $billerCreditAccount,
            'billerCreditBSB' => $billerCreditBSB,
            'billerShortName' => \trim($billerShortName),
            'fileCreationDate' => $fileCreationDate,
            'fileCreationTime' => $fileCreationTime,
            'filler' => $filler,
        ]);
    }

    /**
     * Parse trailer.
     */
    private function processTrailerRecord(string $line): TrailerRecord
    {
        $billerCode = \substr($line, 2, 10);
        $numberOfPayments = \substr($line, 12, 9);
        $amountOfPayments = \substr($line, 21, 15);
        $numberOfErrorCorrections = \substr($line, 36, 9);
        $amountOfErrorCorrections = \substr($line, 45, 15);
        $numberOfReversals = \substr($line, 60, 9);
        $amountOfReversals = \substr($line, 69, 15);
        $settlementAmount = \substr($line, 84, 15);
        $filler = \substr($line, 99, 120);

        return new TrailerRecord([
            'amountOfErrorCorrections' => $this->trimLeftZeros($amountOfErrorCorrections),
            'amountOfPayments' => $this->trimLeftZeros($amountOfPayments),
            'amountOfReversals' => $this->trimLeftZeros($amountOfReversals),
            'billerCode' => $this->trimLeftZeros($billerCode),
            'filler' => $filler,
            'numberOfErrorCorrections' => $this->trimLeftZeros($numberOfErrorCorrections),
            'numberOfPayments' => $this->trimLeftZeros($numberOfPayments),
            'numberOfReversals' => $this->trimLeftZeros($numberOfReversals),
            'settlementAmount' => $this->trimLeftZeros($settlementAmount),
        ]);
    }
}
