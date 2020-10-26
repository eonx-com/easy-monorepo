<?php

declare(strict_types=1);

namespace EonX\EasyBankFiles\Tests\Parsers\DirectEntry;

use EoneoPay\Utils\DateTime;
use EonX\EasyBankFiles\Parsers\DirectEntry\Parser;
use EonX\EasyBankFiles\Parsers\DirectEntry\Results\Header;
use EonX\EasyBankFiles\Parsers\DirectEntry\Results\Trailer;
use EonX\EasyBankFiles\Tests\Parsers\TestCase;

/**
 * @covers \EonX\EasyBankFiles\Parsers\DirectEntry\Parser
 */
final class ParserTest extends TestCase
{
    /**
     * Test if process on parser returns transactions.
     */
    public function testProcessReturnsTransactions(): void
    {
        $contents = $this->getSampleFileContents('DE_return.txt');
        $parser = new Parser($contents);
        self::assertCount(10, $parser->getTransactions());
    }

    /**
     * Test process on parser returns header.
     *
     * @throws \EoneoPay\Utils\Exceptions\InvalidDateTimeStringException
     */
    public function testProcessShouldReturnHeader(): void
    {
        $contents = $this->getSampleFileContents('DE_return.txt');

        $expected = new Header([
            'dateProcessed' => '070905',
            'description' => 'DE Returns  ',
            'userFinancialInstitution' => 'NAB',
            'userIdSupplyingFile' => '012345',
            'userSupplyingFile' => 'NAB                       ',
            'reelSequenceNumber' => '01',
        ]);

        $parser = new Parser($contents);

        // assert the objects are equal
        self::assertEquals($expected, $parser->getHeader());

        // assert the date gets converted to datetime
        self::assertEquals(new DateTime('2005-09-07'), $parser->getHeader()->getDateProcessed());
    }

    /**
     * Test if process on parser returns a trailer record.
     */
    public function testProcessShouldReturnTrailer(): void
    {
        $contents = $this->getSampleFileContents('DE_return.txt');

        $expected = new Trailer([
            'bsb' => '999-999',
            'numberPayments' => '000010',
            'totalNetAmount' => '0000296782',
            'totalCreditAmount' => '0000000000',
            'totalDebitAmount' => '0000296782',
        ]);

        $parser = new Parser($contents);

        // assert the objects are equal
        self::assertEquals($expected, $parser->getTrailer());

        // assert amounts are converted to dollars
        self::assertSame('2967.82', $parser->getTrailer()->getTotalNetAmount());
        self::assertSame('0.00', $parser->getTrailer()->getTotalCreditAmount());
        self::assertSame('2967.82', $parser->getTrailer()->getTotalDebitAmount());
    }

    /**
     * Get sample file contents.
     */
    private function getSampleFileContents(string $file): string
    {
        return \file_get_contents(\realpath(__DIR__) . '/data/' . $file) ?: '';
    }
}
