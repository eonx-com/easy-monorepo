<?php
declare(strict_types=1);

namespace EonX\EasyBankFiles\Tests\Unit\Parsing\BpayBatch\Parser;

use EonX\EasyBankFiles\Parsing\BpayBatch\Parser\BpayBatchParser;
use EonX\EasyBankFiles\Tests\Unit\AbstractUnitTestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Group;

#[CoversClass(BpayBatchParser::class)]
final class BpayBatchParserTest extends AbstractUnitTestCase
{
    /**
     * Should return error from the content.
     */
    #[Group('Batch-Parser-Error')]
    public function testShouldReturnErrors(): void
    {
        $invalidLine = 'invalid';

        $batchParser = new BpayBatchParser($invalidLine);

        $firstError = $batchParser->getErrors()[0];
        self::assertSame(1, $firstError->getLineNumber());
        self::assertSame($invalidLine, $firstError->getLine());
    }

    /**
     * Should return Header object.
     */
    #[Group('Batch-Parser-Header')]
    public function testShouldReturnHeader(): void
    {
        $batchParser = new BpayBatchParser($this->getSampleFileContents('sample.BPB'));

        $header = $batchParser->getHeader();
        self::assertSame('101249', $header->getCustomerId());
        self::assertSame('CustomerShortName', $header->getCustomerShortName());
        self::assertSame('20190717', $header->getDateProcessed());
        self::assertSame('', $header->getRestOfRecord());
    }

    /**
     * Should return trailer from the content.
     */
    #[Group('Batch-Parser-Trailer')]
    public function testShouldReturnTrailer(): void
    {
        $batchParser = new BpayBatchParser($this->getSampleFileContents('sample.BPB'));

        $trailer = $batchParser->getTrailer();
        self::assertSame('342', $trailer->getAmountOfApprovals());
        self::assertSame('0', $trailer->getAmountOfDeclines());
        self::assertSame('342', $trailer->getAmountOfPayments());
        self::assertSame('2', $trailer->getNumberOfApprovals());
        self::assertSame('0', $trailer->getNumberOfDeclines());
        self::assertSame('2', $trailer->getNumberOfPayments());
        self::assertSame('', $trailer->getRestOfRecord());
    }

    /**
     * Should return array of Transaction classes.
     */
    #[Group('Batch-Parser-Transaction')]
    public function testShouldReturnTransaction(): void
    {
        $batchParser = new BpayBatchParser($this->getSampleFileContents('sample.BPB'));

        $transactions = $batchParser->getTransactions();
        self::assertCount(2, $transactions);
        $firstTransactionItem = $transactions[0];
        self::assertSame('162', $firstTransactionItem->getAmount());
        self::assertSame('083170', $firstTransactionItem->getAccountBsb());
        self::assertSame('739813974', $firstTransactionItem->getAccountNumber());
        self::assertSame('254177', $firstTransactionItem->getBillerCode());
        self::assertSame('1444089773', $firstTransactionItem->getCustomerReferenceNumber());
        self::assertSame('', $firstTransactionItem->getReference1());
        self::assertSame('', $firstTransactionItem->getReference2());
        self::assertSame('', $firstTransactionItem->getReference3());
        self::assertSame('', $firstTransactionItem->getRestOfRecord());
        self::assertSame('0000', $firstTransactionItem->getReturnCode());
        self::assertSame('PROCESSED', $firstTransactionItem->getReturnCodeDescription());
        self::assertSame('NAB201907175132940001', $firstTransactionItem->getTransactionReferenceNumber());
    }

    private function getSampleFileContents(string $file): string
    {
        return \file_get_contents(
            \realpath(__DIR__) . '/../../../../../Fixture/Parsing/BpayBatch/' . $file
        ) ?: '';
    }
}
