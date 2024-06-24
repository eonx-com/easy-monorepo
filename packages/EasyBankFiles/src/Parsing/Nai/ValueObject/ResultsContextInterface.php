<?php
declare(strict_types=1);

namespace EonX\EasyBankFiles\Parsing\Nai\ValueObject;

interface ResultsContextInterface
{
    /**
     * Get account for given index.
     */
    public function getAccount(int $index): ?Account;

    /**
     * Get accounts.
     *
     * @return \EonX\EasyBankFiles\Parsing\Nai\ValueObject\Account[]
     */
    public function getAccounts(): array;

    /**
     * Get accounts for given group.
     *
     * @return \EonX\EasyBankFiles\Parsing\Nai\ValueObject\Account[]
     */
    public function getAccountsForGroup(int $group): array;

    /**
     * Get errors.
     *
     * @return \EonX\EasyBankFiles\Parsing\Common\ValueObject\Error[]
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
     * @return \EonX\EasyBankFiles\Parsing\Nai\ValueObject\Group[]
     */
    public function getGroups(): array;

    /**
     * Get transactions.
     *
     * @return \EonX\EasyBankFiles\Parsing\Nai\ValueObject\Transaction[]
     */
    public function getTransactions(): array;

    /**
     * Get transactions for given account.
     *
     * @return \EonX\EasyBankFiles\Parsing\Nai\ValueObject\Transaction[]
     */
    public function getTransactionsForAccount(int $account): array;
}
