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
     * Should return array of DetailRecord classes.
     */
    #[Group('Batch-Parser-Transaction')]
    public function testShouldReturnDetailRecord(): void
    {
        $batchParser = new BpayBatchParser($this->getSampleFileContents('sample.BPB'));

        $detailRecords = $batchParser->getDetailRecords();
        self::assertCount(2, $detailRecords);
        $firstDetailRecordItem = $detailRecords[0];
        self::assertSame('162', $firstDetailRecordItem->getAmount());
        self::assertSame('083170', $firstDetailRecordItem->getAccountBsb());
        self::assertSame('739813974', $firstDetailRecordItem->getAccountNumber());
        self::assertSame('254177', $firstDetailRecordItem->getBillerCode());
        self::assertSame('1444089773', $firstDetailRecordItem->getCustomerReferenceNumber());
        self::assertSame('', $firstDetailRecordItem->getReference1());
        self::assertSame('', $firstDetailRecordItem->getReference2());
        self::assertSame('', $firstDetailRecordItem->getReference3());
        self::assertSame('', $firstDetailRecordItem->getRestOfRecord());
        self::assertSame('0000', $firstDetailRecordItem->getReturnCode());
        self::assertSame('PROCESSED', $firstDetailRecordItem->getReturnCodeDescription());
        self::assertSame('NAB201907175132940001', $firstDetailRecordItem->getTransactionReferenceNumber());
    }

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
     * Should return Header record object.
     */
    #[Group('Batch-Parser-Header')]
    public function testShouldReturnHeaderRecord(): void
    {
        $batchParser = new BpayBatchParser($this->getSampleFileContents('sample.BPB'));

        $header = $batchParser->getHeaderRecord();
        self::assertSame('101249', $header->getCustomerId());
        self::assertSame('CustomerShortName', $header->getCustomerShortName());
        self::assertSame('20190717', $header->getDateProcessed());
        self::assertSame('', $header->getRestOfRecord());
    }

    /**
     * Should return trailer record from the content.
     */
    #[Group('Batch-Parser-Trailer')]
    public function testShouldReturnTrailerRecord(): void
    {
        $batchParser = new BpayBatchParser($this->getSampleFileContents('sample.BPB'));

        $trailer = $batchParser->getTrailerRecord();
        self::assertSame('342', $trailer->getAmountOfApprovals());
        self::assertSame('0', $trailer->getAmountOfDeclines());
        self::assertSame('342', $trailer->getAmountOfPayments());
        self::assertSame('2', $trailer->getNumberOfApprovals());
        self::assertSame('0', $trailer->getNumberOfDeclines());
        self::assertSame('2', $trailer->getNumberOfPayments());
        self::assertSame('', $trailer->getRestOfRecord());
    }

    private function getSampleFileContents(string $file): string
    {
        return \file_get_contents(
            \realpath(__DIR__) . '/../../../../../Fixture/Parsing/BpayBatch/' . $file
        ) ?: '';
    }
}
