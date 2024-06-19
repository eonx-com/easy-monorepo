<?php
declare(strict_types=1);

namespace EonX\EasyBankFiles\Tests\Unit\Generation\Aba\Generator;

use EonX\EasyBankFiles\Generation\Aba\Generator\AbaGenerator;
use EonX\EasyBankFiles\Generation\Aba\ValueObject\DescriptiveRecord;
use EonX\EasyBankFiles\Generation\Aba\ValueObject\FileTotalRecord;
use EonX\EasyBankFiles\Generation\Aba\ValueObject\Transaction;
use EonX\EasyBankFiles\Generation\Common\Exception\InvalidArgumentException;
use EonX\EasyBankFiles\Generation\Common\Exception\LengthMismatchesException;
use EonX\EasyBankFiles\Generation\Common\Exception\ValidationFailedException;
use EonX\EasyBankFiles\Tests\Unit\AbstractUnitTestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Group;

#[CoversClass(AbaGenerator::class)]
final class AbaGeneratorTest extends AbstractUnitTestCase
{
    /**
     * Generator should throw exception when required attributes not set.
     *
     * @throws \EonX\EasyBankFiles\Generation\Common\Exception\InvalidArgumentException
     */
    public function testAttributesWithDefinedRuleAreRequiredException(): void
    {
        $this->expectException(ValidationFailedException::class);

        $transaction = $this->createTransaction();
        $transaction->setAttribute('transactionCode');

        (new AbaGenerator($this->createDescriptiveRecord(), [$transaction]))->getContents();
    }

    /**
     * Generator should throw exception when no transactions given.
     *
     * @throws \EonX\EasyBankFiles\Generation\Common\Exception\InvalidArgumentException
     */
    public function testEmptyTransactionsException(): void
    {
        $this->expectException(InvalidArgumentException::class);

        new AbaGenerator($this->createDescriptiveRecord(), []);
    }

    /**
     * Generator should throw exception when invalid transaction given.
     *
     * @throws \EonX\EasyBankFiles\Generation\Common\Exception\InvalidArgumentException
     */
    public function testInvalidTransactionException(): void
    {
        $this->expectException(InvalidArgumentException::class);

        (new AbaGenerator($this->createDescriptiveRecord(), ['invalid']))->getContents();
    }

    /**
     * Should return contents as string with descriptive record in it.
     *
     * @throws \EonX\EasyBankFiles\Generation\Common\Exception\InvalidArgumentException
     */
    #[Group('Generator-Aba')]
    public function testShouldReturnContents(): void
    {
        $descriptiveRecord = $this->createDescriptiveRecord();
        $generator = new AbaGenerator($descriptiveRecord, [$this->createTransaction()]);

        self::assertNotEmpty($generator->getContents());
        self::assertStringContainsString($descriptiveRecord->getAttributesAsLine(), $generator->getContents());
    }

    /**
     * Should trow exception if DescriptiveRecord's length is greater than 120.
     *
     * @throws \EonX\EasyBankFiles\Generation\Common\Exception\InvalidArgumentException
     */
    #[Group('Generator-Aba')]
    public function testShouldThrowExceptionIfDescriptiveRecordLineExceeds(): void
    {
        $this->expectException(LengthMismatchesException::class);

        $descriptiveRecord = $this->createDescriptiveRecord();
        $descriptiveRecord->setAttribute('nameOfUserSupplyingFile', \str_pad('', 41));

        (new AbaGenerator($descriptiveRecord, [$this->createTransaction()]))->getContents();
    }

    /**
     * Should trow exception if transaction length is greater than 120.
     *
     * @throws \EonX\EasyBankFiles\Generation\Common\Exception\InvalidArgumentException
     */
    #[Group('Generator-Aba')]
    public function testShouldThrowExceptionIfTransactionLineExceeds(): void
    {
        $this->expectException(LengthMismatchesException::class);

        $transaction = $this->createTransaction();
        $transaction->setAttribute('amount', '00000012555');

        (new AbaGenerator($this->createDescriptiveRecord(), [$transaction]))->getContents();
    }

    /**
     * Should throw exception if validation fails.
     *
     * @throws \EonX\EasyBankFiles\Generation\Common\Exception\InvalidArgumentException
     */
    #[Group('Generator-Aba')]
    public function testShouldThrowExceptionIfValidationFails(): void
    {
        $this->expectException(ValidationFailedException::class);

        $descriptiveRecord = $this->createDescriptiveRecord();
        $descriptiveRecord
            ->setAttribute('numberOfUserSupplyingFile', '49262x')
            ->setAttribute('dateToBeProcessed', '10081Q');

        (new AbaGenerator($descriptiveRecord, [$this->createTransaction()]))->getContents();
    }

    /**
     * Should thrown validation exception if BSB format is invalid.
     *
     * @throws \EonX\EasyBankFiles\Generation\Common\Exception\InvalidArgumentException
     */
    #[Group('Generator-Aba')]
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
            (new AbaGenerator($this->createDescriptiveRecord(), [$trans]))->getContents();
        } /** @noinspection PhpRedundantCatchClauseInspection */ catch (ValidationFailedException $exception) {
            self::assertSame($expected, $exception->getErrors()[0]);
        }

        $this->expectException(ValidationFailedException::class);

        $trans->setAttribute('bsbNumber', '111--33');
        (new AbaGenerator($this->createDescriptiveRecord(), [$trans]))->getContents();
    }

    /**
     * Descriptive record, transactions and file total record should be present on the contents.
     *
     * @throws \EonX\EasyBankFiles\Generation\Common\Exception\InvalidArgumentException
     */
    #[Group('Generator-Aba')]
    public function testValuesShouldBePresentInTheContent(): void
    {
        $transactions = [];
        $descriptiveRecord = $this->createDescriptiveRecord();

        $transactions[] = $this->createTransaction();
        $transactions[] = $this->createTransaction(Transaction::CODE_GENERAL_DEBIT);
        /** @var \EonX\EasyBankFiles\Generation\Aba\ValueObject\Transaction $trans */
        $trans = $transactions[0];

        $fileTotalRecord = $this->createFileTotalRecord();

        $generator = new AbaGenerator($descriptiveRecord, $transactions, $fileTotalRecord);

        self::assertStringContainsString($descriptiveRecord->getAttributesAsLine(), $generator->getContents());
        self::assertStringContainsString($trans->getAttributesAsLine(), $generator->getContents());
        self::assertStringContainsString($fileTotalRecord->getAttributesAsLine(), $generator->getContents());
    }

    /**
     * Create a DescriptiveRecord object with default attributes.
     */
    protected function createDescriptiveRecord(): DescriptiveRecord
    {
        return new DescriptiveRecord([
            'reelSequenceNumber' => '01',
            'userFinancialInstitution' => 'UFI',
            'nameOfUserSupplyingFile' => 'LOYALTY CORP AUSTRALIA',
            'numberOfUserSupplyingFile' => 492627,
            'descriptionOfEntries' => 'PAYMENTS',
            'dateToBeProcessed' => '100817',
        ]);
    }

    /**
     * Create File Total Record object with default values.
     */
    protected function createFileTotalRecord(): FileTotalRecord
    {
        return new FileTotalRecord([
            'fileUserNetTotalAmount' => '0',
            'fileUserCreditTotalAmount' => '43452',
            'fileUserDebitTotalAmount' => '43452',
            'fileUserCountOfRecordsType' => '2',
        ]);
    }

    /**
     * Create a Transaction object with default values.
     *
     * @param int|null $transactionCode Either Transaction::CODE_GENERAL_CREDIT or Transaction::CODE_GENERAL_DEBIT
     */
    protected function createTransaction(?int $transactionCode = null): Transaction
    {
        return new Transaction([
            'bsbNumber' => '083-163',
            'accountNumber' => '1234356',
            'transactionCode' => $transactionCode ?? Transaction::CODE_GENERAL_CREDIT,
            'amount' => '0000043452',
            'titleOfAccount' => 'TRUST ME',
            'lodgementReference' => '0049e2d7dd1288d086',
            'traceBsb' => '083-170',
            'traceAccountNumber' => '739827524',
            'nameOfRemitter' => 'TEST PAY RENT RE',
            'amountOfWithholdingTax' => '00000000',
        ]);
    }
}
