<?php
declare(strict_types=1);

namespace EonX\EasyBankFiles\Parsers\Nai\Results;

interface ResultsContextInterface
{
    /**
     * Get account for given index.
     */
    public function getAccount(int $index): ?Account;

    /**
     * Get accounts.
     *
     * @return \EonX\EasyBankFiles\Parsers\Nai\Results\Account[]
     */
    public function getAccounts(): array;

    /**
     * Get accounts for given group.
     *
     * @return \EonX\EasyBankFiles\Parsers\Nai\Results\Account[]
     */
    public function getAccountsForGroup(int $group): array;

    /**
     * Get errors.
     *
     * @return \EonX\EasyBankFiles\Parsers\Error[]
     */
    public function getErrors(): array;

    /**
     * Get file.
     */
    public function getFile(): ?File;

    /**
     * Get group for given index.
     */
    public function getGroup(int $index): ?Group;

    /**
     * Get groups.
     *
     * @return \EonX\EasyBankFiles\Parsers\Nai\Results\Group[]
     */
    public function getGroups(): array;

    /**
     * Get transactions.
     *
     * @return \EonX\EasyBankFiles\Parsers\Nai\Results\Transaction[]
     */
    public function getTransactions(): array;

    /**
     * Get transactions for given account.
     *
     * @return \EonX\EasyBankFiles\Parsers\Nai\Results\Transaction[]
     */
    public function getTransactionsForAccount(int $account): array;
}
