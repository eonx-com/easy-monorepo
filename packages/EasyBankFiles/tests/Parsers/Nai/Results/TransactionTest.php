<?php
declare(strict_types=1);

namespace EonX\EasyBankFiles\Tests\Parsers\Nai\Results;

use EonX\EasyBankFiles\Parsers\Nai\Results\ResultsContextInterface;
use EonX\EasyBankFiles\Parsers\Nai\Results\Transaction;
use EonX\EasyBankFiles\Parsers\Nai\Results\Transactions\Details;
use EonX\EasyBankFiles\Tests\Parsers\TestCase;
use Mockery\MockInterface;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(Transaction::class)]
final class TransactionTest extends TestCase
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
            'transactionDetails' => new Details(),
        ];

        $setExpectations = static function (MockInterface $context) use ($data): void {
            $context
                ->shouldReceive('getAccount')
                ->once()
                ->withArgs([$data['account']])
                ->andReturn(null);
        };

        /** @var \EonX\EasyBankFiles\Parsers\Nai\Results\ResultsContextInterface $context */
        $context = $this->getMockWithExpectations(ResultsContextInterface::class, $setExpectations);

        $transaction = new Transaction($context, $data);

        self::assertNull($transaction->getAccount());
        self::assertSame($data['amount'], $transaction->getAmount());
        self::assertSame($data['code'], $transaction->getCode());
        self::assertSame($data['fundsType'], $transaction->getFundsType());
        self::assertSame($data['referenceNumber'], $transaction->getReferenceNumber());
        self::assertSame($data['text'], $transaction->getText());
        self::assertSame($data['transactionCode'], $transaction->getTransactionCode());
        self::assertInstanceOf(Details::class, $transaction->getTransactionDetails());
    }
}
