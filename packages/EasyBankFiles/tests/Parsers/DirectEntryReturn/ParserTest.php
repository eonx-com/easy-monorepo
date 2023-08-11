<?php
declare(strict_types=1);

namespace EonX\EasyBankFiles\Tests\Parsers\DirectEntryReturn;

use DateTime;
use EonX\EasyBankFiles\Parsers\DirectEntryReturn\Parser;
use EonX\EasyBankFiles\Parsers\DirectEntryReturn\Results\Header;
use EonX\EasyBankFiles\Parsers\DirectEntryReturn\Results\Trailer;
use EonX\EasyBankFiles\Tests\Parsers\TestCase;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(Parser::class)]
final class ParserTest extends TestCase
{
    /**
     * Test if process on parser returns transactions.
     */
    public function testProcessReturnsTransactions(): void
    {
        $parser = new Parser($this->getSampleFileContents('DE_return.txt'));

        $transactions = $parser->getTransactions();
        self::assertCount(10, $transactions);
        $firstTransactionItem = $transactions[0];
        self::assertSame('18622', $firstTransactionItem->getAmount());
        self::assertSame('THOMPSON  SARAH', $firstTransactionItem->getAccountName());
        self::assertSame('458799993', $firstTransactionItem->getAccountNumber());
        self::assertSame('082001', $firstTransactionItem->getBsb());
        self::assertSame('5', $firstTransactionItem->getIndicator());
        self::assertSame('694609', $firstTransactionItem->getLodgmentReference());
        self::assertSame('06', $firstTransactionItem->getOriginalDayOfProcessing());
        self::assertSame('337999', $firstTransactionItem->getOriginalUserIdNumber());
        self::assertSame('2', $firstTransactionItem->getRecordType());
        self::assertSame('SUNNY-PEOPLE', $firstTransactionItem->getRemitterName());
        self::assertSame('010479999', $firstTransactionItem->getTraceAccountNumber());
        self::assertSame('062184', $firstTransactionItem->getTraceBsb());
        self::assertSame('13', $firstTransactionItem->getTxnCode());
    }

    /**
     * Should return error from the content.
     */
    public function testProcessShouldReturnErrors(): void
    {
        $invalidLine = 'invalid';

        $batchParser = new Parser($invalidLine);

        $firstError = $batchParser->getErrors()[0];
        self::assertSame(1, $firstError->getLineNumber());
        self::assertSame($invalidLine, $firstError->getLine());
    }

    /**
     * Test process on parser returns header.
     */
    public function testProcessShouldReturnHeader(): void
    {
        $expected = new Header([
            'dateProcessed' => '070905',
            'description' => 'DE Returns',
            'userFinancialInstitution' => 'NAB',
            'userIdSupplyingFile' => '012345',
            'userSupplyingFile' => 'NAB',
            'reelSequenceNumber' => '01',
        ]);

        $parser = new Parser($this->getSampleFileContents('DE_return.txt'));

        self::assertEquals($expected, $parser->getHeader());
        self::assertEquals(new DateTime('2005-09-07'), $parser->getHeader()->getDateProcessedObject());
    }

    /**
     * Test if process on parser returns a trailer record.
     */
    public function testProcessShouldReturnTrailer(): void
    {
        $expected = new Trailer([
            'bsb' => '999999',
            'numberPayments' => '10',
            'totalNetAmount' => '296782',
            'totalCreditAmount' => '0',
            'totalDebitAmount' => '296782',
        ]);

        $parser = new Parser($this->getSampleFileContents('DE_return.txt'));

        self::assertEquals($expected, $parser->getTrailer());
    }

    /**
     * Get sample file contents.
     */
    private function getSampleFileContents(string $file): string
    {
        return \file_get_contents(\realpath(__DIR__) . '/data/' . $file) ?: '';
    }
}
