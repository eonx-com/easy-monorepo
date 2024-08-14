<?php
declare(strict_types=1);

namespace EonX\EasyBankFiles\Tests\Unit\Parsing\Nai\ValueObject;

use EonX\EasyBankFiles\Parsing\Nai\ValueObject\ResultsContext;
use EonX\EasyBankFiles\Parsing\Nai\ValueObject\Transaction;
use EonX\EasyBankFiles\Parsing\Nai\ValueObject\TransactionDetails;
use EonX\EasyBankFiles\Tests\Unit\AbstractUnitTestCase;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(Transaction::class)]
final class TransactionTest extends AbstractUnitTestCase
{
    /**
     * Result should return data as expected.
     */
    public function testGetDataAsExpected(): void
    {
        $data = [
            'account' => 1,
            'amount' => '12300',
            'code' => '16',
            'fundsType' => 'funds-type',
            'referenceNumber' => 'reference-number',
            'text' => 'text',
            'transactionCode' => '23',
            'transactionDetails' => new TransactionDetails(),
        ];

        $transaction = new Transaction(new ResultsContext([], [], [], [], []), $data);

        self::assertNull($transaction->getAccount());
        self::assertSame($data['amount'], $transaction->getAmount());
        self::assertSame($data['code'], $transaction->getCode());
        self::assertSame($data['fundsType'], $transaction->getFundsType());
        self::assertSame($data['referenceNumber'], $transaction->getReferenceNumber());
        self::assertSame($data['text'], $transaction->getText());
        self::assertSame($data['transactionCode'], $transaction->getTransactionCode());
        self::assertInstanceOf(TransactionDetails::class, $transaction->getTransactionDetails());
    }
}
