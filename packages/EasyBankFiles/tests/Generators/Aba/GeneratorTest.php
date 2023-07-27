<?php
declare(strict_types=1);

namespace EonX\EasyBankFiles\Tests\Generators\Aba;

use EonX\EasyBankFiles\Generators\Aba\Generator;
use EonX\EasyBankFiles\Generators\Aba\Objects\Transaction;
use EonX\EasyBankFiles\Generators\Exceptions\InvalidArgumentException;
use EonX\EasyBankFiles\Generators\Exceptions\LengthMismatchesException;
use EonX\EasyBankFiles\Generators\Exceptions\ValidationFailedException;
use EonX\EasyBankFiles\Tests\Generators\Aba\TestCase as AbaTestCase;

final class GeneratorTest extends AbaTestCase
{
    /**
     * Generator should throw exception when required attributes not set.
     *
     * @throws \EonX\EasyBankFiles\Generators\Exceptions\InvalidArgumentException
     */
    public function testAttributesWithDefinedRuleAreRequiredException(): void
    {
        $this->expectException(ValidationFailedException::class);

        $transaction = $this->createTransaction();
        $transaction->setAttribute('transactionCode');

        (new Generator($this->createDescriptiveRecord(), [$transaction]))->getContents();
    }

    /**
     * Generator should throw exception when no transactions given.
     *
     * @throws \EonX\EasyBankFiles\Generators\Exceptions\InvalidArgumentException
     */
    public function testEmptyTransactionsException(): void
    {
        $this->expectException(InvalidArgumentException::class);

        new Generator($this->createDescriptiveRecord(), []);
    }

    /**
     * Generator should throw exception when invalid transaction given.
     *
     * @throws \EonX\EasyBankFiles\Generators\Exceptions\InvalidArgumentException
     */
    public function testInvalidTransactionException(): void
    {
        $this->expectException(InvalidArgumentException::class);

        (new Generator($this->createDescriptiveRecord(), ['invalid']))->getContents();
    }

    /**
     * Should return contents as string with descriptive record in it.
     *
     * @throws \EonX\EasyBankFiles\Generators\Exceptions\InvalidArgumentException
     *
     * @group Generator-Aba
     */
    public function testShouldReturnContents(): void
    {
        $descriptiveRecord = $this->createDescriptiveRecord();
        $generator = new Generator($descriptiveRecord, [$this->createTransaction()]);

        self::assertNotEmpty($generator->getContents());
        self::assertStringContainsString($descriptiveRecord->getAttributesAsLine(), $generator->getContents());
    }

    /**
     * Should trow exception if DescriptiveRecord's length is greater than 120.
     *
     * @throws \EonX\EasyBankFiles\Generators\Exceptions\InvalidArgumentException
     *
     * @group Generator-Aba
     */
    public function testShouldThrowExceptionIfDescriptiveRecordLineExceeds(): void
    {
        $this->expectException(LengthMismatchesException::class);

        $descriptiveRecord = $this->createDescriptiveRecord();
        $descriptiveRecord->setAttribute('nameOfUserSupplyingFile', \str_pad('', 41));

        (new Generator($descriptiveRecord, [$this->createTransaction()]))->getContents();
    }

    /**
     * Should trow exception if transaction length is greater than 120.
     *
     * @throws \EonX\EasyBankFiles\Generators\Exceptions\InvalidArgumentException
     *
     * @group Generator-Aba
     */
    public function testShouldThrowExceptionIfTransactionLineExceeds(): void
    {
        $this->expectException(LengthMismatchesException::class);

        $transaction = $this->createTransaction();
        $transaction->setAttribute('amount', '00000012555');

        (new Generator($this->createDescriptiveRecord(), [$transaction]))->getContents();
    }

    /**
     * Should throw exception if validation fails.
     *
     * @throws \EonX\EasyBankFiles\Generators\Exceptions\InvalidArgumentException
     *
     * @group Generator-Aba
     */
    public function testShouldThrowExceptionIfValidationFails(): void
    {
        $this->expectException(ValidationFailedException::class);

        $descriptiveRecord = $this->createDescriptiveRecord();
        $descriptiveRecord
            ->setAttribute('numberOfUserSupplyingFile', '49262x')
            ->setAttribute('dateToBeProcessed', '10081Q');

        (new Generator($descriptiveRecord, [$this->createTransaction()]))->getContents();
    }

    /**
     * Should thrown validation exception if BSB format is invalid.
     *
     * @throws \EonX\EasyBankFiles\Generators\Exceptions\InvalidArgumentException
     *
     * @group Generator-Aba
     */
    public function testShouldThrowValidationExceptionIfWrongBSBFormat(): void
    {
        $expected = [
            'attribute' => 'bsbNumber',
            'value' => '1112333',
            'rule' => 'bsb',
        ];

        $trans = $this->createTransaction();
        // Without '-'
        $trans->setAttribute('bsbNumber', '1112333');

        try {
            (new Generator($this->createDescriptiveRecord(), [$trans]))->getContents();
        } /** @noinspection PhpRedundantCatchClauseInspection */ catch (ValidationFailedException $exception) {
            self::assertSame($expected, $exception->getErrors()[0]);
        }

        $this->expectException(ValidationFailedException::class);

        $trans->setAttribute('bsbNumber', '111--33');
        (new Generator($this->createDescriptiveRecord(), [$trans]))->getContents();
    }

    /**
     * Descriptive record, transactions and file total record should be present on the contents.
     *
     * @throws \EonX\EasyBankFiles\Generators\Exceptions\InvalidArgumentException
     *
     * @group Generator-Aba
     */
    public function testValuesShouldBePresentInTheContent(): void
    {
        $transactions = [];
        $descriptiveRecord = $this->createDescriptiveRecord();

        $transactions[] = $this->createTransaction();
        $transactions[] = $this->createTransaction(Transaction::CODE_GENERAL_DEBIT);
        /** @var \EonX\EasyBankFiles\Generators\Aba\Objects\Transaction $trans */
        $trans = $transactions[0];

        $fileTotalRecord = $this->createFileTotalRecord();

        $generator = new Generator($descriptiveRecord, $transactions, $fileTotalRecord);

        self::assertStringContainsString($descriptiveRecord->getAttributesAsLine(), $generator->getContents());
        self::assertStringContainsString($trans->getAttributesAsLine(), $generator->getContents());
        self::assertStringContainsString($fileTotalRecord->getAttributesAsLine(), $generator->getContents());
    }
}
