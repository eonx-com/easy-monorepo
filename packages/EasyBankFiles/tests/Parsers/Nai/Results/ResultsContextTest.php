<?php
declare(strict_types=1);

namespace EonX\EasyBankFiles\Tests\Parsers\Nai\Results;

use EonX\EasyBankFiles\Parsers\Nai\Results\ResultsContext;
use EonX\EasyBankFiles\Tests\Parsers\TestCase;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(ResultsContext::class)]
final class ResultsContextTest extends TestCase
{
    /**
     * Context should return default/empty value when no data set.
     */
    public function testEmptyGettersReturnValueAsExpected(): void
    {
        $context = new ResultsContext([], [], [], [], []);

        self::assertNull($context->getAccount(0));
        self::assertIsArray($context->getAccounts());
        self::assertIsArray($context->getAccountsForGroup(1));
        self::assertIsArray($context->getErrors());
        self::assertNull($context->getFile());
        self::assertNull($context->getGroup(0));
        self::assertIsArray($context->getGroups());
        self::assertIsArray($context->getTransactions());
        self::assertIsArray($context->getTransactionsForAccount(1));
    }

    /**
     * Context should create errors as expected.
     */
    public function testErrorsInRecords(): void
    {
        $accounts = [
            [
                'identifier' => [
                    'line' => '',
                    'line_number' => 1,
                ],
                'group' => 1,
                'trailer' => [
                    'line' => '',
                    'line_number' => 2,
                ],
            ],
            [],
        ];
        $groups = [[]];
        $transactions = [
            [
                'line' => '',
                'line_number' => 2,
            ],
        ];

        $context = new ResultsContext($accounts, [], [], $groups, $transactions);

        self::assertCount(4, $context->getErrors());
    }
}
