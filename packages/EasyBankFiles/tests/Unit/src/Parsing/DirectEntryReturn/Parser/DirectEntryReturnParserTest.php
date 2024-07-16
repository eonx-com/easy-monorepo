<?php
declare(strict_types=1);

namespace EonX\EasyBankFiles\Tests\Unit\Parsing\DirectEntryReturn\Parser;

use DateTime;
use EonX\EasyBankFiles\Parsing\DirectEntryReturn\Parser\DirectEntryReturnParser;
use EonX\EasyBankFiles\Parsing\DirectEntryReturn\ValueObject\HeaderRecord;
use EonX\EasyBankFiles\Parsing\DirectEntryReturn\ValueObject\TrailerRecord;
use EonX\EasyBankFiles\Tests\Unit\AbstractUnitTestCase;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(DirectEntryReturnParser::class)]
final class DirectEntryReturnParserTest extends AbstractUnitTestCase
{
    /**
     * Test if process on parser returns detail records.
     */
    public function testProcessReturnsDetailRecords(): void
    {
        $parser = new DirectEntryReturnParser($this->getSampleFileContents('DE_return.txt'));

        $detailRecords = $parser->getDetailRecords();
        self::assertCount(10, $detailRecords);
        $firstDetailRecord = $detailRecords[0];
        self::assertSame('18622', $firstDetailRecord->getAmount());
        self::assertSame('THOMPSON  SARAH', $firstDetailRecord->getAccountName());
        self::assertSame('458799993', $firstDetailRecord->getAccountNumber());
        self::assertSame('082001', $firstDetailRecord->getBsb());
        self::assertSame('5', $firstDetailRecord->getReturnCode());
        self::assertSame('694609', $firstDetailRecord->getLodgmentReference());
        self::assertSame('06', $firstDetailRecord->getOriginalDayOfProcessing());
        self::assertSame('337999', $firstDetailRecord->getOriginalUserIdNumber());
        self::assertSame('2', $firstDetailRecord->getRecordType());
        self::assertSame('SUNNY-PEOPLE', $firstDetailRecord->getRemitterName());
        self::assertSame('010479999', $firstDetailRecord->getTraceAccountNumber());
        self::assertSame('062184', $firstDetailRecord->getTraceBsb());
        self::assertSame('13', $firstDetailRecord->getTransactionCode());
    }

    /**
     * Should return error from the content.
     */
    public function testProcessShouldReturnErrors(): void
    {
        $invalidLine = 'invalid';

        $parser = new DirectEntryReturnParser($invalidLine);

        $firstError = $parser->getErrors()[0];
        self::assertSame(1, $firstError->getLineNumber());
        self::assertSame($invalidLine, $firstError->getLine());
    }

    /**
     * Test process on parser returns header record.
     */
    public function testProcessShouldReturnHeaderRecord(): void
    {
        $expected = new HeaderRecord([
            'dateProcessed' => '070905',
            'description' => 'DE Returns',
            'directEntryUserId' => '012345',
            'mnemonicOfFinancialInstitution' => 'NAB',
            'mnemonicOfSendingMember' => 'NAB',
            'reelSequenceNumber' => '01',
        ]);

        $parser = new DirectEntryReturnParser($this->getSampleFileContents('DE_return.txt'));

        self::assertEquals($expected, $parser->getHeaderRecord());
        self::assertEquals(new DateTime('2005-09-07'), $parser->getHeaderRecord()->getDateProcessedObject());
    }

    /**
     * Test if process on parser returns a trailer record.
     */
    public function testProcessShouldReturnTrailer(): void
    {
        $expected = new TrailerRecord([
            'bsb' => '999999',
            'totalCreditAmount' => '0',
            'totalDebitAmount' => '296782',
            'totalNetAmount' => '296782',
            'totalRecordCount' => '10',
        ]);

        $parser = new DirectEntryReturnParser($this->getSampleFileContents('DE_return.txt'));

        self::assertEquals($expected, $parser->getTrailerRecord());
    }

    private function getSampleFileContents(string $file): string
    {
        return \file_get_contents(
            \realpath(__DIR__) . '/../../../../../Fixture/Parsing/DirectEntryReturn/' . $file
        ) ?: '';
    }
}
