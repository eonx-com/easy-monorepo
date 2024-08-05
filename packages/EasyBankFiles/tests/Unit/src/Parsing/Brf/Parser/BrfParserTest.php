<?php
declare(strict_types=1);

namespace EonX\EasyBankFiles\Tests\Unit\Parsing\Brf\Parser;

use EonX\EasyBankFiles\Parsing\Brf\Parser\BrfParser;
use EonX\EasyBankFiles\Tests\Unit\AbstractUnitTestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Group;

#[CoversClass(BrfParser::class)]
final class BrfParserTest extends AbstractUnitTestCase
{
    /**
     * Should return array of DetailRecord classes.
     */
    #[Group('Brf-Parser-Detail-Record')]
    public function testShouldReturnDetailRecord(): void
    {
        $brfParser = new BrfParser($this->getSampleFileContents('sample.BRF'));

        $detailRecords = $brfParser->getDetailRecords();
        self::assertCount(3, $detailRecords);
        $firstDetailRecord = $detailRecords[0];
        self::assertSame('55000', $firstDetailRecord->getAmount());
        self::assertSame('254169', $firstDetailRecord->getBillerCode());
        self::assertSame('4370658181', $firstDetailRecord->getCustomerReferenceNumber());
        self::assertSame('000', $firstDetailRecord->getErrorCorrectionReason());
        self::assertSame('', $firstDetailRecord->getOriginalReferenceNumber());
        self::assertSame('05', $firstDetailRecord->getPaymentInstructionType());
        self::assertSame('062726', $firstDetailRecord->getPaymentTime());
        self::assertSame('CBA201605260146337726', $firstDetailRecord->getTransactionReferenceNumber());
        self::assertSame('20160526', $firstDetailRecord->getPaymentDate());
        self::assertSame('20160526', $firstDetailRecord->getSettlementDate());
        self::assertSame('', $firstDetailRecord->getFiller());
    }

    /**
     * Should return error from the content.
     */
    #[Group('Brf-Parser-Error')]
    public function testShouldReturnErrors(): void
    {
        $invalidLine = 'invalid';

        $brfParser = new BrfParser($invalidLine);

        $firstError = $brfParser->getErrors()[0];
        self::assertSame(1, $firstError->getLineNumber());
        self::assertSame($invalidLine, $firstError->getLine());
    }

    /**
     * Should return Header object.
     */
    #[Group('Brf-Parser-Header')]
    public function testShouldReturnHeaderRecord(): void
    {
        $brfParser = new BrfParser($this->getSampleFileContents('sample.BRF'));

        $headerRecord = $brfParser->getHeaderRecord();
        self::assertSame('254169', $headerRecord->getBillerCode());
        self::assertSame('739827524', $headerRecord->getBillerCreditAccount());
        self::assertSame('083170', $headerRecord->getBillerCreditBSB());
        self::assertSame('REAL ESTATE CLOUD', $headerRecord->getBillerShortName());
        self::assertSame('20160526', $headerRecord->getFileCreationDate());
        self::assertSame('203541', $headerRecord->getFileCreationTime());
        self::assertSame('', $headerRecord->getFiller());
    }

    /**
     * Should return trailer from the content.
     */
    #[Group('Brf-Parser-Trailer')]
    public function testShouldReturnTrailerRecord(): void
    {
        $brfParser = new BrfParser($this->getSampleFileContents('sample.BRF'));

        $trailerRecord = $brfParser->getTrailerRecord();
        self::assertSame('254169', $trailerRecord->getBillerCode());
        self::assertSame('', $trailerRecord->getFiller());
        self::assertSame([
            'amount' => '0',
            'type' => 'credit',
        ], $trailerRecord->getAmountOfErrorCorrections());
        self::assertSame([
            'amount' => '116025',
            'type' => 'debit',
        ], $trailerRecord->getAmountOfPayments());
        self::assertSame([
            'amount' => '0',
            'type' => 'credit',
        ], $trailerRecord->getAmountOfReversals());
        self::assertSame([
            'amount' => '0',
            'type' => 'credit',
        ], $trailerRecord->getNumberOfErrorCorrections());
        self::assertSame([
            'amount' => '2',
            'type' => 'credit',
        ], $trailerRecord->getNumberOfPayments());
        self::assertSame([
            'amount' => '0',
            'type' => 'credit',
        ], $trailerRecord->getNumberOfReversals());
        self::assertSame([
            'amount' => '115000',
            'type' => 'credit',
        ], $trailerRecord->getSettlementAmount());
    }

    private function getSampleFileContents(string $file): string
    {
        return \file_get_contents(
            \realpath(__DIR__) . '/../../../../../Fixture/Parsing/Brf/' . $file
        ) ?: '';
    }
}
