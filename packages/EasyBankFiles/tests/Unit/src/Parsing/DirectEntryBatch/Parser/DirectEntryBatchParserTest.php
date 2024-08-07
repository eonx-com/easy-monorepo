<?php
declare(strict_types=1);

namespace EonX\EasyBankFiles\Tests\Unit\Parsing\DirectEntryBatch\Parser;

use DateTime;
use EonX\EasyBankFiles\Parsing\DirectEntryBatch\Parser\DirectEntryBatchParser;
use EonX\EasyBankFiles\Parsing\DirectEntryBatch\ValueObject\PaymentDetailRecord;
use EonX\EasyBankFiles\Parsing\DirectEntryBatch\ValueObject\RefusalDetailRecord;
use EonX\EasyBankFiles\Parsing\DirectEntryBatch\ValueObject\ReturnDetailRecord;
use EonX\EasyBankFiles\Tests\Unit\AbstractUnitTestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;

#[CoversClass(DirectEntryBatchParser::class)]
final class DirectEntryBatchParserTest extends AbstractUnitTestCase
{
    /**
     * @see testProcessSucceeds
     */
    public static function provideCorrectFile(): iterable
    {
        yield 'Nines, empty line, End-of-File' => [
            'fileName' => 'correct.one-batch.nde',
        ];

        yield 'Nines, End-of-File' => [
            'fileName' => 'correct.one-batch2.nde',
        ];

        yield 'Empty line, End-of-File (without nines)' => [
            'fileName' => 'correct.one-batch3.nde',
        ];

        yield 'End-of-File (without nines and empty line)' => [
            'fileName' => 'correct.one-batch4.nde',
        ];
    }

    /**
     * @see testProcessReturnsErrors
     */
    public static function provideFileWithErrors(): iterable
    {
        yield 'Incorrect data' => [
            'fileName' => 'incorrect.incorrect-data.nde',
            'expectedErrorsCount' => 1,
            'invalidLine' => 'incorrect-data',
        ];

        yield 'Partial data, missed transaction' => [
            'fileName' => 'incorrect.missed-payment-detail-record.nde',
            'expectedErrorsCount' => 1,
            'invalidLine' => '7999-999            000020000000002000000000000000                        000002',
        ];

        yield 'Partial data, missed trailer' => [
            'fileName' => 'incorrect.missed-file-total-record.nde',
            'expectedErrorsCount' => 3,
            'invalidLine' => '0502027019.39.52  01CRU       TEST                      123456TEST        ' .
                '261119027019.39.52                  CUSCAL-NDE',
        ];

        yield 'Partial data, missed header' => [
            'fileName' => 'incorrect.missed-descriptive-record.nde',
            'expectedErrorsCount' => 2,
            'invalidLine' => '2123-456123456789 130000080000SAMPLE                          SAMPLE' .
                '            987-654987654321SAMPLE          00000000',
        ];
    }

    #[DataProvider('provideFileWithErrors')]
    public function testProcessReturnsErrors(string $fileName, int $expectedErrorsCount, string $invalidLine): void
    {
        $parser = new DirectEntryBatchParser($this->getSampleFileContents($fileName));

        $errors = $parser->getErrors();

        self::assertCount($expectedErrorsCount, $errors);
        self::assertSame($invalidLine, $errors[0]->getLine());
    }

    public function testProcessReturnsErrorsAndParseOnlyCorrectBatches(): void
    {
        $parser = new DirectEntryBatchParser(
            $this->getSampleFileContents('incorrect.multiple-batches-with-errors.nde')
        );

        $batches = $parser->getBatches();
        self::assertCount(3, $batches);
        self::assertCount(5, $batches[0]->getRecords());
        self::assertCount(3, $batches[1]->getRecords());
        self::assertCount(1, $batches[2]->getRecords());
        self::assertCount(10, $parser->getErrors());
    }

    public function testProcessShouldReturnErrors(): void
    {
        $invalidLine = 'invalid';

        $batchParser = new DirectEntryBatchParser($invalidLine);

        $firstError = $batchParser->getErrors()[0];
        self::assertSame(1, $firstError->getLineNumber());
        self::assertSame($invalidLine, $firstError->getLine());
    }

    /**
     * @dataProvider provideCorrectFile
     */
    public function testProcessSucceeds(string $fileName): void
    {
        $parser = new DirectEntryBatchParser($this->getSampleFileContents($fileName));

        $batches = $parser->getBatches();
        self::assertCount(1, $batches);
        $descriptiveRecord = $batches[0]->getDescriptiveRecord();
        self::assertSame('261119', $descriptiveRecord->getDateProcessed());
        self::assertSame('TEST', $descriptiveRecord->getDescriptionOfEntries());
        self::assertSame('01', $descriptiveRecord->getReelSequenceNumber());
        self::assertSame('CRU', $descriptiveRecord->getUserFinancialInstitution());
        self::assertSame('123456', $descriptiveRecord->getNumberOfUserSupplyingFile());
        self::assertSame('TEST', $descriptiveRecord->getNameOfUserSupplyingFile());
        $paymentDetailRecord = $batches[0]->getRecords()[0];
        self::assertInstanceOf(PaymentDetailRecord::class, $paymentDetailRecord);
        self::assertSame('100000', $paymentDetailRecord->getAmount());
        self::assertSame('TEST 1', $paymentDetailRecord->getAccountName());
        self::assertSame('123456789', $paymentDetailRecord->getAccountNumber());
        self::assertSame('123456', $paymentDetailRecord->getBsb());
        self::assertSame(' ', $paymentDetailRecord->getIndicator());
        self::assertSame('TEST', $paymentDetailRecord->getLodgmentReference());
        self::assertSame('1', $paymentDetailRecord->getRecordType());
        self::assertSame('TEST', $paymentDetailRecord->getRemitterName());
        self::assertSame('987654321', $paymentDetailRecord->getTraceAccountNumber());
        self::assertSame('987654', $paymentDetailRecord->getTraceBsb());
        self::assertSame('50', $paymentDetailRecord->getTransactionCode());
        self::assertSame('0', $paymentDetailRecord->getAmountOfWithholdingTax());
        self::assertCount(0, $parser->getErrors());
        $returnDetailRecord = $batches[0]->getRecords()[1];
        self::assertInstanceOf(ReturnDetailRecord::class, $returnDetailRecord);
        self::assertSame('100000', $returnDetailRecord->getAmount());
        self::assertSame('TEST 2', $returnDetailRecord->getAccountName());
        self::assertSame('123456789', $returnDetailRecord->getAccountNumber());
        self::assertSame('123456', $returnDetailRecord->getBsb());
        self::assertSame(' ', $returnDetailRecord->getReturnCode());
        self::assertSame('TEST', $returnDetailRecord->getLodgmentReference());
        self::assertSame('2', $returnDetailRecord->getRecordType());
        self::assertSame('TEST', $returnDetailRecord->getRemitterName());
        self::assertSame('987654321', $returnDetailRecord->getTraceAccountNumber());
        self::assertSame('988654', $returnDetailRecord->getTraceBsb());
        self::assertSame('50', $returnDetailRecord->getTransactionCode());
        self::assertSame('01', $returnDetailRecord->getOriginalDayOfProcessing());
        self::assertSame('101', $returnDetailRecord->getOriginalUserIdNumber());
        $refusalDetailRecord = $batches[0]->getRecords()[2];
        self::assertInstanceOf(RefusalDetailRecord::class, $refusalDetailRecord);
        self::assertSame('100000', $refusalDetailRecord->getAmount());
        self::assertSame('TEST 2', $refusalDetailRecord->getAccountName());
        self::assertSame('123456789', $refusalDetailRecord->getAccountNumber());
        self::assertSame('123456', $refusalDetailRecord->getBsb());
        self::assertSame(' ', $refusalDetailRecord->getRefusalCode());
        self::assertSame('TEST', $refusalDetailRecord->getLodgmentReference());
        self::assertSame('3', $refusalDetailRecord->getRecordType());
        self::assertSame('TEST', $refusalDetailRecord->getRemitterName());
        self::assertSame('987654321', $refusalDetailRecord->getTraceAccountNumber());
        self::assertSame('988654', $refusalDetailRecord->getTraceBsb());
        self::assertSame('50', $refusalDetailRecord->getTransactionCode());
        self::assertSame('02', $refusalDetailRecord->getOriginalDayOfReturn());
        self::assertSame('102', $refusalDetailRecord->getOriginalUserIdNumber());
        $fileTotalRecord = $batches[0]->getFileTotalRecord();
        self::assertSame('999999', $fileTotalRecord->getBsb());
        self::assertSame('2', $fileTotalRecord->getTotalRecordCount());
        self::assertSame('200000', $fileTotalRecord->getTotalCreditAmount());
        self::assertSame('0', $fileTotalRecord->getTotalDebitAmount());
        self::assertSame('200000', $fileTotalRecord->getTotalNetAmount());
        self::assertCount(0, $parser->getErrors());
    }

    public function testProcessSucceedsWithDeReturnFile(): void
    {
        $parser = new DirectEntryBatchParser($this->getSampleFileContents('DE_return.txt'));

        $batches = $parser->getBatches();
        self::assertCount(1, $batches);
        $descriptiveRecord = $batches[0]->getDescriptiveRecord();
        self::assertSame('070905', $descriptiveRecord->getDateProcessed());
        self::assertSame('DE Returns', $descriptiveRecord->getDescriptionOfEntries());
        self::assertSame('01', $descriptiveRecord->getReelSequenceNumber());
        self::assertSame('NAB', $descriptiveRecord->getUserFinancialInstitution());
        self::assertSame('012345', $descriptiveRecord->getNumberOfUserSupplyingFile());
        self::assertSame('NAB', $descriptiveRecord->getNameOfUserSupplyingFile());
        self::assertEquals(new DateTime('2005-09-07'), $batches[0]->getDescriptiveRecord()->getDateProcessedObject());
        $records = $batches[0]->getRecords();
        self::assertCount(10, $records);
        $firstRecord = $records[0];
        self::assertSame('18622', $firstRecord->getAmount());
        self::assertSame('THOMPSON  SARAH', $firstRecord->getAccountName());
        self::assertSame('458799993', $firstRecord->getAccountNumber());
        self::assertSame('082001', $firstRecord->getBsb());
        self::assertSame('5', $firstRecord->getReturnCode());
        self::assertSame('694609', $firstRecord->getLodgmentReference());
        self::assertSame('2', $firstRecord->getRecordType());
        self::assertSame('SUNNY-PEOPLE', $firstRecord->getRemitterName());
        self::assertSame('010479999', $firstRecord->getTraceAccountNumber());
        self::assertSame('062184', $firstRecord->getTraceBsb());
        self::assertSame('13', $firstRecord->getTransactionCode());
        self::assertSame('06', $firstRecord->getOriginalDayOfProcessing());
        self::assertSame('337999', $firstRecord->getOriginalUserIdNumber());
        $fileTotalRecord = $batches[0]->getFileTotalRecord();
        self::assertSame('999999', $fileTotalRecord->getBsb());
        self::assertSame('10', $fileTotalRecord->getTotalRecordCount());
        self::assertSame('0', $fileTotalRecord->getTotalCreditAmount());
        self::assertSame('296782', $fileTotalRecord->getTotalDebitAmount());
        self::assertSame('296782', $fileTotalRecord->getTotalNetAmount());
    }

    public function testProcessSucceedsWithMultipleBatches(): void
    {
        $parser = new DirectEntryBatchParser($this->getSampleFileContents('correct.multiple-batches.nde'));

        $batches = $parser->getBatches();
        self::assertCount(2, $batches);
        $descriptiveRecord = $batches[0]->getDescriptiveRecord();
        self::assertSame('261119', $descriptiveRecord->getDateProcessed());
        self::assertSame('TEST', $descriptiveRecord->getDescriptionOfEntries());
        self::assertSame('01', $descriptiveRecord->getReelSequenceNumber());
        self::assertSame('CRU', $descriptiveRecord->getUserFinancialInstitution());
        self::assertSame('123456', $descriptiveRecord->getNumberOfUserSupplyingFile());
        self::assertSame('TEST', $descriptiveRecord->getNameOfUserSupplyingFile());
        $records = $batches[0]->getRecords()[0];
        self::assertSame('100000', $records->getAmount());
        self::assertSame('TEST 1', $records->getAccountName());
        self::assertSame('123456789', $records->getAccountNumber());
        self::assertSame('123456', $records->getBsb());
        self::assertSame(' ', $records->getIndicator());
        self::assertSame('TEST', $records->getLodgmentReference());
        self::assertSame('1', $records->getRecordType());
        self::assertSame('TEST', $records->getRemitterName());
        self::assertSame('987654321', $records->getTraceAccountNumber());
        self::assertSame('987654', $records->getTraceBsb());
        self::assertSame('50', $records->getTransactionCode());
        self::assertSame('0', $records->getAmountOfWithholdingTax());
        self::assertCount(0, $parser->getErrors());
        $fileTotalRecord = $batches[0]->getFileTotalRecord();
        self::assertSame('999999', $fileTotalRecord->getBsb());
        self::assertSame('2', $fileTotalRecord->getTotalRecordCount());
        self::assertSame('200000', $fileTotalRecord->getTotalCreditAmount());
        self::assertSame('0', $fileTotalRecord->getTotalDebitAmount());
        self::assertSame('200000', $fileTotalRecord->getTotalNetAmount());
        $descriptiveRecord = $batches[1]->getDescriptiveRecord();
        self::assertSame('261119', $descriptiveRecord->getDateProcessed());
        self::assertSame('TEST', $descriptiveRecord->getDescriptionOfEntries());
        self::assertSame('01', $descriptiveRecord->getReelSequenceNumber());
        self::assertSame('CRU', $descriptiveRecord->getUserFinancialInstitution());
        self::assertSame('123456', $descriptiveRecord->getNumberOfUserSupplyingFile());
        self::assertSame('TEST2', $descriptiveRecord->getNameOfUserSupplyingFile());
        $records = $batches[1]->getRecords()[0];
        self::assertSame('100000', $records->getAmount());
        self::assertSame('TEST 2', $records->getAccountName());
        self::assertSame('123456789', $records->getAccountNumber());
        self::assertSame('123456', $records->getBsb());
        self::assertSame(' ', $records->getIndicator());
        self::assertSame('TEST', $records->getLodgmentReference());
        self::assertSame('1', $records->getRecordType());
        self::assertSame('TEST', $records->getRemitterName());
        self::assertSame('987654321', $records->getTraceAccountNumber());
        self::assertSame('987654', $records->getTraceBsb());
        self::assertSame('50', $records->getTransactionCode());
        self::assertSame('0', $records->getAmountOfWithholdingTax());
        self::assertCount(0, $parser->getErrors());
        $fileTotalRecord = $batches[1]->getFileTotalRecord();
        self::assertSame('999997', $fileTotalRecord->getBsb());
        self::assertSame('2', $fileTotalRecord->getTotalRecordCount());
        self::assertSame('200000', $fileTotalRecord->getTotalCreditAmount());
        self::assertSame('0', $fileTotalRecord->getTotalDebitAmount());
        self::assertSame('200000', $fileTotalRecord->getTotalNetAmount());
    }

    private function getSampleFileContents(string $file): string
    {
        return \file_get_contents(
            \realpath(__DIR__) . '/../../../../../Fixture/Parsing/DirectEntryBatch/' . $file
        ) ?: '';
    }
}
