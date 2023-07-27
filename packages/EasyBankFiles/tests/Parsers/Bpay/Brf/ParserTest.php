<?php
declare(strict_types=1);

namespace EonX\EasyBankFiles\Tests\Parsers\Bpay\Brf;

use EonX\EasyBankFiles\Parsers\Bpay\Brf\Parser;
use EonX\EasyBankFiles\Tests\Parsers\TestCase;

/**
 * @covers \EonX\EasyBankFiles\Parsers\Bpay\Brf\Parser
 */
final class ParserTest extends TestCase
{
    /**
     * Should return error from the content.
     *
     * @group Brf-Parser-Error
     */
    public function testShouldReturnErrors(): void
    {
        $invalidLine = 'invalid';

        $batchParser = new Parser($invalidLine);

        $firstError = $batchParser->getErrors()[0];
        self::assertSame(1, $firstError->getLineNumber());
        self::assertSame($invalidLine, $firstError->getLine());
    }

    /**
     * Should return Header object.
     *
     * @group Brf-Parser-Header
     */
    public function testShouldReturnHeader(): void
    {
        $brfParser = new Parser($this->getSampleFileContents('sample.BRF'));

        $header = $brfParser->getHeader();
        self::assertSame('254169', $header->getBillerCode());
        self::assertSame('739827524', $header->getBillerCreditAccount());
        self::assertSame('083170', $header->getBillerCreditBSB());
        self::assertSame('REAL ESTATE CLOUD', $header->getBillerShortName());
        self::assertSame('20160526', $header->getFileCreationDate());
        self::assertSame('203541', $header->getFileCreationTime());
        self::assertSame('', $header->getRestOfRecord());
    }

    /**
     * Should return trailer from the content.
     *
     * @group Brf-Parser-Trailer
     */
    public function testShouldReturnTrailer(): void
    {
        $brfParser = new Parser($this->getSampleFileContents('sample.BRF'));

        $trailer = $brfParser->getTrailer();
        self::assertSame('254169', $trailer->getBillerCode());
        self::assertSame('', $trailer->getRestOfRecord());
        self::assertSame([
            'amount' => '0',
            'type' => 'credit',
        ], $trailer->getAmountOfErrorCorrections());
        self::assertSame([
            'amount' => '116025',
            'type' => 'debit',
        ], $trailer->getAmountOfPayments());
        self::assertSame([
            'amount' => '0',
            'type' => 'credit',
        ], $trailer->getAmountOfReversals());
        self::assertSame([
            'amount' => '0',
            'type' => 'credit',
        ], $trailer->getNumberOfErrorCorrections());
        self::assertSame([
            'amount' => '2',
            'type' => 'credit',
        ], $trailer->getNumberOfPayments());
        self::assertSame([
            'amount' => '0',
            'type' => 'credit',
        ], $trailer->getNumberOfReversals());
        self::assertSame([
            'amount' => '115000',
            'type' => 'credit',
        ], $trailer->getSettlementAmount());
    }

    /**
     * Should return array of Transaction classes.
     *
     * @group Brf-Parser-Transaction
     */
    public function testShouldReturnTransaction(): void
    {
        $brfParser = new Parser($this->getSampleFileContents('sample.BRF'));

        $transactions = $brfParser->getTransactions();
        self::assertCount(3, $transactions);
        $firstTransactionItem = $transactions[0];
        self::assertSame('55000', $firstTransactionItem->getAmount());
        self::assertSame('254169', $firstTransactionItem->getBillerCode());
        self::assertSame('4370658181', $firstTransactionItem->getCustomerReferenceNumber());
        self::assertSame('000', $firstTransactionItem->getErrorCorrectionReason());
        self::assertSame('', $firstTransactionItem->getOriginalReferenceNumber());
        self::assertSame('05', $firstTransactionItem->getPaymentInstructionType());
        self::assertSame('062726', $firstTransactionItem->getPaymentTime());
        self::assertSame('CBA201605260146337726', $firstTransactionItem->getTransactionReferenceNumber());
        self::assertSame('20160526', $firstTransactionItem->getPaymentDate());
        self::assertSame('20160526', $firstTransactionItem->getSettlementDate());
        self::assertSame('', $firstTransactionItem->getRestOfRecord());
    }

    /**
     * Get sample file contents.
     */
    private function getSampleFileContents(string $file): string
    {
        return \file_get_contents(\realpath(__DIR__) . '/data/' . $file) ?: '';
    }
}
