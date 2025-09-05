<?php
declare(strict_types=1);

namespace EonX\EasyBankFiles\Parsing\Nai\ValueObject;

use EonX\EasyBankFiles\Parsing\Common\ValueObject\Error;
use EonX\EasyBankFiles\Parsing\Nai\ValueObject\AccountIdentifier as AccountIdentifier;
use EonX\EasyBankFiles\Parsing\Nai\ValueObject\AccountTrailer as AccountTrailer;
use EonX\EasyBankFiles\Parsing\Nai\ValueObject\FileHeader as FilerHeader;
use EonX\EasyBankFiles\Parsing\Nai\ValueObject\FileTrailer as FileTrailer;
use EonX\EasyBankFiles\Parsing\Nai\ValueObject\GroupHeader as GroupHeader;
use EonX\EasyBankFiles\Parsing\Nai\ValueObject\GroupTrailer as GroupTrailer;

final class ResultsContext
{
    use AccountSummaryCodesTrait;
    use TransactionDetailCodesTrait;

    /**
     * @var \EonX\EasyBankFiles\Parsing\Nai\ValueObject\Account[]
     */
    private array $accounts = [];

    private array $caching = [];

    /**
     * @var \EonX\EasyBankFiles\Parsing\Common\ValueObject\Error[]
     */
    private array $errors = [];

    private ?File $file = null;

    /**
     * @var \EonX\EasyBankFiles\Parsing\Nai\ValueObject\Group[]
     */
    private array $groups = [];

    /**
     * @var \EonX\EasyBankFiles\Parsing\Nai\ValueObject\Transaction[]
     */
    private array $transactions = [];

    public function __construct(
        array $accounts,
        array $errors,
        array $file,
        array $groups,
        array $transactions,
        private bool $isBai = false,
    ) {
        // Not proud of that, but the order matters, DO NOT change it
        $this
            ->initTransactions($transactions)
            ->initAccounts($accounts)
            ->initErrors($errors)
            ->initFile($file)
            ->initGroups($groups);
    }

    public function getAccount(int $index): ?Account
    {
        return $this->accounts[$index] ?? null;
    }

    public function getAccounts(): array
    {
        return $this->accounts;
    }

    public function getAccountsForGroup(int $group): array
    {
        return $this->caching[\sprintf('group_%d_accounts', $group)] ?? [];
    }

    public function getErrors(): array
    {
        return $this->errors;
    }

    public function getFile(): ?File
    {
        return $this->file;
    }

    public function getGroup(int $index): ?Group
    {
        return $this->groups[$index] ?? null;
    }

    public function getGroups(): array
    {
        return $this->groups;
    }

    public function getTransactions(): array
    {
        return $this->transactions;
    }

    public function getTransactionsForAccount(int $account): array
    {
        return $this->caching[\sprintf('account_%d_transactions', $account)] ?? [];
    }

    /**
     * Add error.
     */
    private function addError(string $line, int $lineNumber): void
    {
        $this->errors[] = new Error(\compact('line', 'lineNumber'));
    }

    /**
     * Cache account for given group.
     */
    private function cacheAccount(int $group, Account $account): void
    {
        $this->cacheResult(\sprintf('group_%d_accounts', $group), $account);
    }

    /**
     * Cache result for given key.
     */
    private function cacheResult(string $key, mixed $result): void
    {
        if (isset($this->caching[$key]) === false) {
            $this->caching[$key] = [];
        }

        $this->caching[$key][] = $result;
    }

    /**
     * Cache transaction for given account.
     */
    private function cacheTransaction(int $account, Transaction $transaction): void
    {
        $this->cacheResult(\sprintf('account_%d_transactions', $account), $transaction);
    }

    /**
     * Format account identifier transactions and add code summary.
     */
    private function formatTransactionCodes(array $transactionCodes): array
    {
        foreach ($transactionCodes as $key => $codes) {
            $code = $this->removeTrailingSlash($codes[0] ?? '');
            $amount = $this->removeTrailingSlash($codes[1] ?? '');

            $transactionCodes[$key] = [
                'amount' => $amount,
                'code' => $code,
                'description' => $this->getCodeSummary($code),
            ];
        }

        return $transactionCodes;
    }

    /**
     * Get data from line as an associative array using given attributes. If line structure invalid, return null.
     *
     * @param string[] $attributes |null
     */
    private function getDataFromLine(array $attributes, string $line, int $lineNumber): ?array
    {
        $data = [];
        /** @var string[] $lineArray */
        $lineArray = \explode(',', $line);

        foreach ($attributes as $index => $attribute) {
            // If one attribute is missing from the file, return null
            if (isset($lineArray[$index]) === false) {
                $this->addError($line, $lineNumber);

                return null;
            }

            $data[$attribute] = $lineArray[$index];
        }

        return $data;
    }

    /**
     * Get transaction data from line as an associative array using given attributes.
     * If line structure invalid, return null. If the last element in data is missing,
     * its alright as transaction might not have a text.
     */
    private function getTransactionDataFromLine(string $line, int $lineNumber): ?array
    {
        $lineArray = \explode(',', $line);
        $cloneLineArray = $lineArray;
        $fundsType = \strtolower($lineArray[3] ?? '');

        // Keep fundsType Z check for backward compatibility for NAB files in AU
        if ($fundsType === 'z') {
            $this->isBai = true;
        }

        $required = ['code', 'transactionCode', 'amount', 'fundsType'];
        $optional = $this->isBai
            ? ['referenceNumber', 'customerReferenceNumber', 'text']
            : ['referenceNumber', 'text'];

        if ($fundsType === 's') {
            $required[] = 'immediateAvailabilityAmount';
            $required[] = 'oneDayAvailabilityAmount';
            $required[] = 'plusTwoDayAvailabilityAmount';
        }

        if ($fundsType === 'v') {
            $required[] = 'valueDate';
            $required[] = 'valueTime';
        }

        $data = [];
        $attributes = [...$required, ...$optional];

        foreach ($attributes as $index => $attribute) {
            $value = $lineArray[$index] ?? '';
            $endsWithSlash = \str_ends_with($value, '/');
            $data[$attribute] = $endsWithSlash ? \substr($value, 0, -1) : $value;

            // Remove processed item from clone array, so we can use remaining ones for text at the end
            unset($cloneLineArray[$index]);

            // If attribute ends with slash, it's the last one of line, exit
            if ($endsWithSlash) {
                break;
            }
        }

        // Validate all required and optional attributes are defined
        foreach ($attributes as $attribute) {
            if (isset($data[$attribute]) === true && $data[$attribute] !== '') {
                continue;
            }

            // If this is a required attribute fail and return
            if (\in_array($attribute, $required, true)) {
                // Add error if data is either null or empty string
                $this->addError($line, $lineNumber);

                // Stop processing this line
                return null;
            }

            // Otherwise set a default value to it
            $data[$attribute] = '';
        }

        // Text field in BAI files can contain commas, so we need to join remaining items in the array
        if (\count($cloneLineArray) > 0) {
            $data['text'] .= ',' . \implode(',', $cloneLineArray);
        }

        // Trim text
        $data['text'] = \trim($data['text'] ?? '');

        return $data;
    }

    /**
     * Instantiate account identifier.
     */
    private function initAccountIdentifier(array $identifier): ?AccountIdentifier
    {
        $attributes = ['code', 'commercialAccountNumber', 'currencyCode'];
        $data = $this->getDataFromLine($attributes, $identifier['line'], $identifier['line_number']);

        if ($data === null) {
            $this->addError($identifier['line'], $identifier['line_number']);

            return null;
        }

        /**
         * So from 4th item onwards are Transaction code and Amount
         * We can group them in pairs [transactionCode, Amount]
         *
         * But first let remove the first 3 elements.
         */
        $transactionCodes = \array_slice(\explode(',', (string)$identifier['line']), 3);
        $transactionCodes = $this->formatTransactionCodes(\array_chunk($transactionCodes, 2));

        return new AccountIdentifier(\array_merge($data, [
            'transactionCodes' => $transactionCodes,
        ]));
    }

    /**
     * Instantiate account trailer.
     */
    private function initAccountTrailer(array $trailer): ?AccountTrailer
    {
        return $this->instantiateSimpleItem([
            'code',
            'accountControlTotalA',
            'accountControlTotalB',
        ], AccountTrailer::class, $trailer);
    }

    /**
     * Instantiate accounts.
     */
    private function initAccounts(array $accounts): self
    {
        foreach ($accounts as $index => $account) {
            if (isset($account['identifier'], $account['trailer']) === false) {
                continue;
            }

            $accountResult = $this->instantiateNaiResult(Account::class, [
                // Indexes coming from parser start from 1, we want 0
                'group' => $account['group'] - 1,
                'identifier' => $this->initAccountIdentifier($account['identifier']),
                'index' => $index,
                'trailer' => $this->initAccountTrailer($account['trailer']),
            ]);

            $this->accounts[] = $accountResult;
            $this->cacheAccount($account['group'], $accountResult);
        }

        return $this;
    }

    /**
     * Instantiate errors.
     */
    private function initErrors(array $errors): self
    {
        foreach ($errors as $error) {
            $this->addError($error['line'], $error['line_number']);
        }

        return $this;
    }

    /**
     * Instantiate file.
     */
    private function initFile(array $file): self
    {
        if (isset($file['header'], $file['trailer']) === false) {
            return $this;
        }

        $this->file = $this->instantiateNaiResult(File::class, [
            'header' => $this->initFileHeader($file['header']),
            'trailer' => $this->initFileTrailer($file['trailer']),
        ]);

        return $this;
    }

    /**
     * Instantiate file header.
     */
    private function initFileHeader(array $header): ?FilerHeader
    {
        return $this->instantiateSimpleItem([
            'code',
            'senderId',
            'receiverId',
            'fileCreationDate',
            'fileCreationTime',
            'fileSequenceNumber',
            'physicalRecordLength',
            'blockingFactor',
        ], FilerHeader::class, $header);
    }

    /**
     * Instantiate file trailer.
     */
    private function initFileTrailer(array $trailer): ?FileTrailer
    {
        $attributes = ['code', 'fileControlTotalA', 'numberOfGroups', 'numberOfRecords'];

        // No fileControlTotalB in BAI files
        if ($this->isBai === false) {
            $attributes[] = 'fileControlTotalB';
        }

        return $this->instantiateSimpleItem($attributes, FileTrailer::class, $trailer);
    }

    /**
     * Instantiate group header.
     */
    private function initGroupHeader(array $header): ?GroupHeader
    {
        return $this->instantiateSimpleItem([
            'code',
            'ultimateReceiverId',
            'originatorReceiverId',
            'groupStatus',
            'asOfDate',
            'asOfTime',
        ], GroupHeader::class, $header);
    }

    /**
     * Instantiate group trailer.
     */
    private function initGroupTrailer(array $trailer): ?GroupTrailer
    {
        return $this->instantiateSimpleItem([
            'code',
            'groupControlTotalA',
            'numberOfAccounts',
            'groupControlTotalB',
        ], GroupTrailer::class, $trailer);
    }

    /**
     * Instantiate groups.
     */
    private function initGroups(array $groups): self
    {
        foreach ($groups as $index => $group) {
            if (isset($group['header'], $group['trailer']) === false) {
                continue;
            }

            $this->groups[] = $this->instantiateNaiResult(Group::class, [
                'header' => $this->initGroupHeader($group['header']),
                'index' => $index,
                'trailer' => $this->initGroupTrailer($group['trailer']),
            ]);
        }

        return $this;
    }

    /**
     * Instantiate transactions.
     */
    private function initTransactions(array $transactions): self
    {
        foreach ($transactions as $transaction) {
            $data = $this->getTransactionDataFromLine($transaction['line'], $transaction['line_number']);

            if ($data === null) {
                continue;
            }

            $transactionResult = $this->instantiateNaiResult(Transaction::class, \array_merge($data, [
                // Indexes coming from parser start from 1, we want 0
                'account' => $transaction['account'] - 1,
                'transactionDetails' => $this->getTransactionCodeDetails($data['transactionCode']),
            ]));

            $this->transactions[] = $transactionResult;
            $this->cacheTransaction($transaction['account'], $transactionResult);
        }

        return $this;
    }

    /**
     * Instantiate Nai result object and pass the context as parameter.
     *
     * @template T of object
     *
     * @param class-string<T> $resultClass
     *
     * @return T
     */
    private function instantiateNaiResult(string $resultClass, array $data): object
    {
        return new $resultClass($this, $data);
    }

    /**
     * Instantiate simple item for given attributes, class and array.
     *
     * @template T of object
     *
     * @param string[] $attributes
     * @param class-string<T> $class
     *
     * @return T|null
     */
    private function instantiateSimpleItem(array $attributes, string $class, array $item): ?object
    {
        $data = $this->getDataFromLine($attributes, $item['line'], $item['line_number']);

        if ($data === null) {
            return null;
        }

        return new $class($data);
    }

    private function removeTrailingSlash(string $value): string
    {
        return \str_replace('/', '', $value);
    }
}
