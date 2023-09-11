<?php
declare(strict_types=1);

namespace EonX\EasyBankFiles\Tests\Parsers\Nai\Results;

use EonX\EasyBankFiles\Parsers\Nai\Results\AbstractNaiResult;
use EonX\EasyBankFiles\Parsers\Nai\Results\Account;
use EonX\EasyBankFiles\Parsers\Nai\Results\Accounts\Identifier;
use EonX\EasyBankFiles\Parsers\Nai\Results\Accounts\Trailer;
use EonX\EasyBankFiles\Parsers\Nai\Results\ResultsContextInterface;
use EonX\EasyBankFiles\Tests\Parsers\TestCase;
use Mockery\MockInterface;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(AbstractNaiResult::class)]
#[CoversClass(Account::class)]
final class AccountTest extends TestCase
{
    /**
     * Result should return data as expected.
     */
    public function testGetDataAsExpected(): void
    {
        $data = [
            'group' => 1,
            'identifier' => new Identifier(),
            'index' => 2,
            'trailer' => new Trailer(),
        ];

        $setExpectations = static function (MockInterface $context) use ($data): void {
            $context
                ->shouldReceive('getGroup')
                ->once()
                ->withArgs([$data['group']])
                ->andReturn(null);
            $context
                ->shouldReceive('getTransactionsForAccount')
                ->once()
                ->withArgs([$data['index']])
                ->andReturn([]);
        };

        /** @var \EonX\EasyBankFiles\Parsers\Nai\Results\ResultsContextInterface $context */
        $context = $this->getMockWithExpectations(ResultsContextInterface::class, $setExpectations);

        $account = new Account($context, $data);

        self::assertInstanceOf(Identifier::class, $account->getIdentifier());
        self::assertNull($account->getGroup());
        self::assertIsArray($account->getTransactions());
        self::assertInstanceOf(Trailer::class, $account->getTrailer());
    }
}
