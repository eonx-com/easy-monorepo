<?php

declare(strict_types=1);

namespace EonX\EasyBankFiles\Tests\Parsers\DirectEntryBatch;

use DateTime;
use EonX\EasyBankFiles\Parsers\DirectEntryBatch\Parser;
use EonX\EasyBankFiles\Parsers\DirectEntryBatch\Results\Batch\TransactionTypePayment;
use EonX\EasyBankFiles\Parsers\DirectEntryBatch\Results\Batch\TransactionTypeRefusal;
use EonX\EasyBankFiles\Parsers\DirectEntryBatch\Results\Batch\TransactionTypeReturn;
use EonX\EasyBankFiles\Tests\Parsers\TestCase;

/**
 * @covers \EonX\EasyBankFiles\Parsers\DirectEntryBatch\Parser
 */
final class ParserTest extends TestCase
{
    /**
     * @return iterable<mixed>
     *
     * @see testProcessSucceeds
     */
    public function provideCorrectFile(): iterable
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
     * @return iterable<mixed>
     *
     * @see testProcessReturnsErrors
     */
    public function provideFileWithErrors(): iterable
    {
        yield 'Incorrect data' => [
            'fileName' => 'incorrect.incorrect-data.nde',
            'expectedErrorsCount' => 1,
            'invalidLine' => 'incorrect-data',
        ];

        yield 'Partial data, missed transaction' => [
            'fileName' => 'incorrect.missed-transaction.nde',
            'expectedErrorsCount' => 1,
            'invalidLine' => '7999-999            000020000000002000000000000000                        000002',
        ];

        yield 'Partial data, missed trailer' => [
            'fileName' => 'incorrect.missed-trailer.nde',
            'expectedErrorsCount' => 3,
            'invalidLine' => '0502027019.39.52  01CRU       TEST                      123456TEST        ' .
                '261119027019.39.52                  CUSCAL-NDE',
        ];

        yield 'Partial data, missed header' => [
            'fileName' => 'incorrect.missed-header.nde',
            'expectedErrorsCount' => 2,
            'invalidLine' => '2123-456123456789 130000080000SAMPLE                          SAMPLE' .
                '            987-654987654321SAMPLE          00000000',
        ];
    }

    /**
     * @dataProvider provideFileWithErrors
     */
    public function testProcessReturnsErrors(string $fileName, int $expectedErrorsCount, string $invalidLine): void
    {
        $parser = new Parser($this->getSampleFileContents($fileName));

        $errors = $parser->getErrors();

        self::assertCount($expectedErrorsCount, $errors);
        self::assertSame($invalidLine, $errors[0]->getLine());
    }

    public function testProcessReturnsErrorsAndParseOnlyCorrectBatches(): void
    {
        $parser = new Parser($this->getSampleFileContents('incorrect.multiple-batches-with-errors.nde'));

        $batches = $parser->getBatches();
        self::assertCount(3, $batches);
        self::assertCount(5, $batches[0]->getTransactions());
        self::assertCount(3, $batches[1]->getTransactions());
        self::assertCount(1, $batches[2]->getTransactions());
        self::assertCount(10, $parser->getErrors());
    }

    public function testProcessShouldReturnErrors(): void
    {
        $invalidLine = 'invalid';

        $batchParser = new Parser($invalidLine);

        $firstError = $batchParser->getErrors()[0];
        self::assertSame(1, $firstError->getLineNumber());
        self::assertSame($invalidLine, $firstError->getLine());
    }

    /**
     * @dataProvider provideCorrectFile
     */
    public function testProcessSucceeds(string $fileName): void
    {
        $parser = new Parser($this->getSampleFileContents($fileName));

        $batches = $parser->getBatches();
        self::assertCount(1, $batches);
        $header = $batches[0]->getHeader();
        self::assertSame('261119', $header->getDateProcessed());
        self::assertSame('TEST', $header->getDescription());
        self::assertSame('01', $header->getReelSequenceNumber());
        self::assertSame('CRU', $header->getUserFinancialInstitution());
        self::assertSame('123456', $header->getUserIdSupplyingFile());
        self::assertSame('TEST', $header->getUserSupplyingFile());
        $transactionTypePayment = $batches[0]->getTransactions()[0];
        self::assertInstanceOf(TransactionTypePayment::class, $transactionTypePayment);
        self::assertSame('100000', $transactionTypePayment->getAmount());
        self::assertSame('TEST 1', $transactionTypePayment->getAccountName());
        self::assertSame('123456789', $transactionTypePayment->getAccountNumber());
        self::assertSame('123456', $transactionTypePayment->getBsb());
        self::assertSame(' ', $transactionTypePayment->getIndicator());
        self::assertSame('TEST', $transactionTypePayment->getLodgmentReference());
        self::assertSame('1', $transactionTypePayment->getRecordType());
        self::assertSame('TEST', $transactionTypePayment->getRemitterName());
        self::assertSame('987654321', $transactionTypePayment->getTraceAccountNumber());
        self::assertSame('987654', $transactionTypePayment->getTraceBsb());
        self::assertSame('50', $transactionTypePayment->getTxnCode());
        self::assertSame('0', $transactionTypePayment->getWithholdingTax());
        self::assertCount(0, $parser->getErrors());
        $transactionTypeReturn = $batches[0]->getTransactions()[1];
        self::assertInstanceOf(TransactionTypeReturn::class, $transactionTypeReturn);
        self::assertSame('100000', $transactionTypeReturn->getAmount());
        self::assertSame('TEST 2', $transactionTypeReturn->getAccountName());
        self::assertSame('123456789', $transactionTypeReturn->getAccountNumber());
        self::assertSame('123456', $transactionTypeReturn->getBsb());
        self::assertSame(' ', $transactionTypeReturn->getIndicator());
        self::assertSame('TEST', $transactionTypeReturn->getLodgmentReference());
        self::assertSame('2', $transactionTypeReturn->getRecordType());
        self::assertSame('TEST', $transactionTypeReturn->getRemitterName());
        self::assertSame('987654321', $transactionTypeReturn->getTraceAccountNumber());
        self::assertSame('988654', $transactionTypeReturn->getTraceBsb());
        self::assertSame('50', $transactionTypeReturn->getTxnCode());
        self::assertSame('01', $transactionTypeReturn->getOriginalDayOfProcessing());
        self::assertSame('101', $transactionTypeReturn->getOriginalUserIdNumber());
        $transactionTypeRefusal = $batches[0]->getTransactions()[2];
        self::assertInstanceOf(TransactionTypeRefusal::class, $transactionTypeRefusal);
        self::assertSame('100000', $transactionTypeRefusal->getAmount());
        self::assertSame('TEST 2', $transactionTypeRefusal->getAccountName());
        self::assertSame('123456789', $transactionTypeRefusal->getAccountNumber());
        self::assertSame('123456', $transactionTypeRefusal->getBsb());
        self::assertSame(' ', $transactionTypeRefusal->getIndicator());
        self::assertSame('TEST', $transactionTypeRefusal->getLodgmentReference());
        self::assertSame('3', $transactionTypeRefusal->getRecordType());
        self::assertSame('TEST', $transactionTypeRefusal->getRemitterName());
        self::assertSame('987654321', $transactionTypeRefusal->getTraceAccountNumber());
        self::assertSame('988654', $transactionTypeRefusal->getTraceBsb());
        self::assertSame('50', $transactionTypeRefusal->getTxnCode());
        self::assertSame('02', $transactionTypeRefusal->getOriginalDayOfReturn());
        self::assertSame('102', $transactionTypeRefusal->getOriginalUserIdNumber());
        $trailer = $batches[0]->getTrailer();
        self::assertSame('999999', $trailer->getBsb());
        self::assertSame('2', $trailer->getNumberPayments());
        self::assertSame('200000', $trailer->getTotalCreditAmount());
        self::assertSame('0', $trailer->getTotalDebitAmount());
        self::assertSame('200000', $trailer->getTotalNetAmount());
        self::assertCount(0, $parser->getErrors());
    }

    public function testProcessSucceedsWithDeReturnFile(): void
    {
        $parser = new Parser($this->getSampleFileContents('DE_return.txt'));

        $batches = $parser->getBatches();
        self::assertCount(1, $batches);
        $header = $batches[0]->getHeader();
        self::assertSame('070905', $header->getDateProcessed());
        self::assertSame('DE Returns', $header->getDescription());
        self::assertSame('01', $header->getReelSequenceNumber());
        self::assertSame('NAB', $header->getUserFinancialInstitution());
        self::assertSame('012345', $header->getUserIdSupplyingFile());
        self::assertSame('NAB', $header->getUserSupplyingFile());
        self::assertEquals(new DateTime('2005-09-07'), $batches[0]->getHeader()->getDateProcessedObject());
        $transactions = $batches[0]->getTransactions();
        self::assertCount(10, $transactions);
        $firstTransactionItem = $transactions[0];
        self::assertSame('18622', $firstTransactionItem->getAmount());
        self::assertSame('THOMPSON  SARAH', $firstTransactionItem->getAccountName());
        self::assertSame('458799993', $firstTransactionItem->getAccountNumber());
        self::assertSame('082001', $firstTransactionItem->getBsb());
        self::assertSame('5', $firstTransactionItem->getIndicator());
        self::assertSame('694609', $firstTransactionItem->getLodgmentReference());
        self::assertSame('2', $firstTransactionItem->getRecordType());
        self::assertSame('SUNNY-PEOPLE', $firstTransactionItem->getRemitterName());
        self::assertSame('010479999', $firstTransactionItem->getTraceAccountNumber());
        self::assertSame('062184', $firstTransactionItem->getTraceBsb());
        self::assertSame('13', $firstTransactionItem->getTxnCode());
        self::assertSame('06', $firstTransactionItem->getOriginalDayOfProcessing());
        self::assertSame('337999', $firstTransactionItem->getOriginalUserIdNumber());
        $trailer = $batches[0]->getTrailer();
        self::assertSame('999999', $trailer->getBsb());
        self::assertSame('10', $trailer->getNumberPayments());
        self::assertSame('0', $trailer->getTotalCreditAmount());
        self::assertSame('296782', $trailer->getTotalDebitAmount());
        self::assertSame('296782', $trailer->getTotalNetAmount());
    }

    public function testProcessSucceedsWithMultipleBatches(): void
    {
        $parser = new Parser($this->getSampleFileContents('correct.multiple-batches.nde'));

        $batches = $parser->getBatches();
        self::assertCount(2, $batches);
        $header = $batches[0]->getHeader();
        self::assertSame('261119', $header->getDateProcessed());
        self::assertSame('TEST', $header->getDescription());
        self::assertSame('01', $header->getReelSequenceNumber());
        self::assertSame('CRU', $header->getUserFinancialInstitution());
        self::assertSame('123456', $header->getUserIdSupplyingFile());
        self::assertSame('TEST', $header->getUserSupplyingFile());
        $transaction = $batches[0]->getTransactions()[0];
        self::assertSame('100000', $transaction->getAmount());
        self::assertSame('TEST 1', $transaction->getAccountName());
        self::assertSame('123456789', $transaction->getAccountNumber());
        self::assertSame('123456', $transaction->getBsb());
        self::assertSame(' ', $transaction->getIndicator());
        self::assertSame('TEST', $transaction->getLodgmentReference());
        self::assertSame('1', $transaction->getRecordType());
        self::assertSame('TEST', $transaction->getRemitterName());
        self::assertSame('987654321', $transaction->getTraceAccountNumber());
        self::assertSame('987654', $transaction->getTraceBsb());
        self::assertSame('50', $transaction->getTxnCode());
        self::assertSame('0', $transaction->getWithholdingTax());
        self::assertCount(0, $parser->getErrors());
        $trailer = $batches[0]->getTrailer();
        self::assertSame('999999', $trailer->getBsb());
        self::assertSame('2', $trailer->getNumberPayments());
        self::assertSame('200000', $trailer->getTotalCreditAmount());
        self::assertSame('0', $trailer->getTotalDebitAmount());
        self::assertSame('200000', $trailer->getTotalNetAmount());
        $header = $batches[1]->getHeader();
        self::assertSame('261119', $header->getDateProcessed());
        self::assertSame('TEST', $header->getDescription());
        self::assertSame('01', $header->getReelSequenceNumber());
        self::assertSame('CRU', $header->getUserFinancialInstitution());
        self::assertSame('123456', $header->getUserIdSupplyingFile());
        self::assertSame('TEST2', $header->getUserSupplyingFile());
        $transaction = $batches[1]->getTransactions()[0];
        self::assertSame('100000', $transaction->getAmount());
        self::assertSame('TEST 2', $transaction->getAccountName());
        self::assertSame('123456789', $transaction->getAccountNumber());
        self::assertSame('123456', $transaction->getBsb());
        self::assertSame(' ', $transaction->getIndicator());
        self::assertSame('TEST', $transaction->getLodgmentReference());
        self::assertSame('1', $transaction->getRecordType());
        self::assertSame('TEST', $transaction->getRemitterName());
        self::assertSame('987654321', $transaction->getTraceAccountNumber());
        self::assertSame('987654', $transaction->getTraceBsb());
        self::assertSame('50', $transaction->getTxnCode());
        self::assertSame('0', $transaction->getWithholdingTax());
        self::assertCount(0, $parser->getErrors());
        $trailer = $batches[1]->getTrailer();
        self::assertSame('999997', $trailer->getBsb());
        self::assertSame('2', $trailer->getNumberPayments());
        self::assertSame('200000', $trailer->getTotalCreditAmount());
        self::assertSame('0', $trailer->getTotalDebitAmount());
        self::assertSame('200000', $trailer->getTotalNetAmount());
    }

    /**
     * Get sample file contents.
     */
    private function getSampleFileContents(string $file): string
    {
        return \file_get_contents(\realpath(__DIR__) . '/data/' . $file) ?: '';
    }
}
