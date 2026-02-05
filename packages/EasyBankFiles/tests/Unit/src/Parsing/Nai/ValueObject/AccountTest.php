<?php
declare(strict_types=1);

namespace EonX\EasyBankFiles\Tests\Unit\Parsing\Nai\ValueObject;

use EonX\EasyBankFiles\Parsing\Nai\ValueObject\AbstractNaiResult;
use EonX\EasyBankFiles\Parsing\Nai\ValueObject\Account;
use EonX\EasyBankFiles\Parsing\Nai\ValueObject\AccountIdentifier;
use EonX\EasyBankFiles\Parsing\Nai\ValueObject\AccountTrailer;
use EonX\EasyBankFiles\Parsing\Nai\ValueObject\ResultsContext;
use EonX\EasyBankFiles\Tests\Unit\AbstractUnitTestCase;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(AbstractNaiResult::class)]
#[CoversClass(Account::class)]
final class AccountTest extends AbstractUnitTestCase
{
    /**
     * Result should return data as expected.
     */
    public function testGetDataAsExpected(): void
    {
        $data = [
            'group' => 1,
            'identifier' => new AccountIdentifier(),
            'index' => 2,
            'trailer' => new AccountTrailer(),
        ];

        $account = new Account(new ResultsContext([], [], [], [], []), $data);

        self::assertInstanceOf(AccountIdentifier::class, $account->getIdentifier());
        self::assertNull($account->getGroup());
        self::assertSame([], $account->getTransactions());
        self::assertInstanceOf(AccountTrailer::class, $account->getTrailer());
    }
}
