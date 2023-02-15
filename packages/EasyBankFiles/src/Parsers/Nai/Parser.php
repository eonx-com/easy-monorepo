<?php

declare(strict_types=1);

namespace EonX\EasyBankFiles\Parsers\Nai;

use EonX\EasyBankFiles\Parsers\AbstractLineByLineParser;
use EonX\EasyBankFiles\Parsers\Nai\Results\File;
use EonX\EasyBankFiles\Parsers\Nai\Results\ResultsContext;
use Nette\Utils\Strings;

final class Parser extends AbstractLineByLineParser
{
    /**
     * @var string
     */
    private const ACCOUNT_IDENTIFIER = '03';

    /**
     * @var string
     */
    private const ACCOUNT_TRAILER = '49';

    /**
     * @var string
     */
    private const CONTINUATION = '88';

    /**
     * @var string
     */
    private const FILE_HEADER = '01';

    /**
     * @var string
     */
    private const FILE_TRAILER = '99';

    /**
     * @var string
     */
    private const GROUP_HEADER = '02';

    /**
     * @var string
     */
    private const GROUP_TRAILER = '98';

    /**
     * @var string
     */
    private const TRANSACTION_DETAIL = '16';

    /**
     * @var mixed[]
     */
    private $accounts = [];

    /**
     * @var int|null
     */
    private $currentAccount;

    /**
     * @var int|null
     */
    private $currentGroup;

    /**
     * @var int
     */
    private $currentLineNumber;

    /**
     * @var int|null
     */
    private $currentTransaction;

    /**
     * @var mixed[]
     */
    private $errors = [];

    /**
     * @var mixed[]
     */
    private $file = [];

    /**
     * @var mixed[]
     */
    private $groups = [];

    /**
     * @var string
     */
    private $previousCode;

    /**
     * @var bool
     */
    private $previousFull = true;

    /**
     * @var \EonX\EasyBankFiles\Parsers\Nai\Results\ResultsContext
     */
    private $resultsContext;

    /**
     * @var mixed[]
     */
    private $transactions = [];

    /**
     * Get accounts.
     *
     * @return \EonX\EasyBankFiles\Parsers\Nai\Results\Account[]
     */
    public function getAccounts(): array
    {
        return $this->resultsContext->getAccounts();
    }

    /**
     * Get errors.
     *
     * @return \EonX\EasyBankFiles\Parsers\Error[]
     */
    public function getErrors(): array
    {
        return $this->resultsContext->getErrors();
    }

    /**
     * Get file.
     */
    public function getFile(): ?File
    {
        return $this->resultsContext->getFile();
    }

    /**
     * Get groups.
     *
     * @return \EonX\EasyBankFiles\Parsers\Nai\Results\Group[]
     */
    public function getGroups(): array
    {
        return $this->resultsContext->getGroups();
    }

    /**
     * Get transactions.
     *
     * @return \EonX\EasyBankFiles\Parsers\Nai\Results\Transaction[]
     */
    public function getTransactions(): array
    {
        return $this->resultsContext->getTransactions();
    }

    /**
     * Parse given contents and instantiate results context.
     */
    protected function process(): void
    {
        parent::process();

        $this->resultsContext = new ResultsContext(
            $this->accounts,
            $this->errors,
            $this->file,
            $this->groups,
            $this->transactions
        );
    }

    /**
     * Process line and parse data.
     */
    protected function processLine(int $lineNumber, string $line): void
    {
        $code = \substr($line, 0, 2);

        // Set current line number
        $this->currentLineNumber = $lineNumber;

        // If current code not valid, create error and skip to next line
        if ($this->isCodeValid($code) === false) {
            $this->addError($line);

            return;
        }

        // Sanitize full lines
        $currentLineIsFull = $this->checkFullLine($line);
        $line = $this->sanitizeFullLine($line);

        // If continuation, update previous and skip to next line
        if ($code === self::CONTINUATION) {
            $this->continuePrevious($line);
            $this->previousFull = $currentLineIsFull;

            return;
        }

        // Current code becomes then previous one for next continuation
        $this->previousCode = $code;
        $this->previousFull = $currentLineIsFull;

        switch ($code) {
            case self::ACCOUNT_IDENTIFIER:
                $this->currentAccount = ($this->currentAccount ?? 0) + 1;
                $this->addAccountIdentifier($this->currentAccount, $line);
                break;
            case self::ACCOUNT_TRAILER:
                $this->addAccountTrailer($this->currentAccount ?? 0, $line);
                break;
            case self::FILE_HEADER:
                $this->file['header'] = $this->setItem($line);
                break;
            case self::FILE_TRAILER:
                $this->file['trailer'] = $this->setItem($line);
                break;
            case self::GROUP_HEADER:
                $this->currentGroup = \count($this->groups) + 1;
                $this->addGroupHeader($this->currentGroup, $line);
                break;
            case self::GROUP_TRAILER:
                $this->addGroupTrailer($this->currentGroup ?? 0, $line);
                break;
            case self::TRANSACTION_DETAIL:
                $this->currentTransaction = ($this->currentTransaction ?? 0) + 1;
                $this->addTransaction($line);
                break;
        }
    }

    /**
     * Add header to given account.
     */
    private function addAccountIdentifier(int $account, string $identifier): void
    {
        // If current group is null, it means that the file structure is wrong so error
        if ($this->currentGroup === null) {
            $this->addError($identifier);

            return;
        }

        if (isset($this->accounts[$account]) === false) {
            $this->accounts[$account] = [
                'group' => $this->currentGroup,
            ];
        }

        $this->accounts[$account]['identifier'] = $this->setItem($identifier);
    }

    /**
     * Add trailer to given account.
     */
    private function addAccountTrailer(int $account, string $trailer): void
    {
        // If account not already created it means that the file structure is wrong
        if (isset($this->accounts[$account]) === false) {
            $this->addError($trailer);

            return;
        }

        $this->accounts[$account]['trailer'] = $this->setItem($trailer);
    }

    /**
     * Add error.
     */
    private function addError(string $line): void
    {
        $this->errors[] = $this->setItem($line);
    }

    /**
     * Add header to given group.
     */
    private function addGroupHeader(int $group, string $header): void
    {
        if (isset($this->groups[$group]) === false) {
            $this->groups[$group] = [];
        }

        $this->groups[$group]['header'] = $this->setItem($header);
    }

    /**
     * Add trailer to given group.
     */
    private function addGroupTrailer(int $group, string $trailer): void
    {
        // If group not already created it means that the file structure is wrong
        if (isset($this->groups[$group]) === false) {
            $this->addError($trailer);

            return;
        }

        $this->groups[$group]['trailer'] = $this->setItem($trailer);
    }

    /**
     * Add transaction.
     */
    private function addTransaction(string $transaction): void
    {
        // If current account is null, it means that the file structure is wrong so error
        if ($this->currentAccount === null) {
            $this->addError($transaction);

            return;
        }

        $this->transactions[$this->currentTransaction] = [
            'account' => $this->currentAccount,
            'line' => $transaction,
            'line_number' => $this->currentLineNumber,
        ];
    }

    /**
     * Check if this line has full contents in it. If the line ends with a /
     * it means it's a full line.
     */
    private function checkFullLine(string $line): bool
    {
        // Prevent logic to add extra coma on continuation logic if already there
        return Strings::endsWith($line, '/') && Strings::endsWith($line, ',/') === false;
    }

    /**
     * Continue account line for given index.
     */
    private function continueAccount(string $index, string $line): void
    {
        if (isset($this->accounts[$this->currentAccount][$index]['line']) === false) {
            $this->addError($line);

            return;
        }

        $this->accounts[$this->currentAccount][$index]['line'] .= $line;

//        \var_dump('__continue account__');
//        \var_dump($this->accounts);
    }

    /**
     * Continue file line for given index.
     */
    private function continueFile(string $index, string $line): void
    {
        $this->file[$index]['line'] .= $line;
    }

    /**
     * Continue group line for given index.
     */
    private function continueGroup(string $index, string $line): void
    {
        if (isset($this->groups[$this->currentGroup][$index]['line']) === false) {
            $this->addError($line);

            return;
        }

        $this->groups[$this->currentGroup][$index]['line'] .= $line;
    }

    /**
     * Continue previous line.
     */
    private function continuePrevious(string $line): void
    {
        // Remove 88, from the current line
        $line = \substr($line, 3);

        // Add coma at the start of the line if previous record fits completely on the line
        if ($this->previousFull) {
            $line = ',' . $line;
        }

        switch ($this->previousCode) {
            case self::ACCOUNT_IDENTIFIER:
                $this->continueAccount('identifier', $line);
                break;
            case self::ACCOUNT_TRAILER:
                $this->continueAccount('trailer', $line);
                break;
            case self::FILE_HEADER:
                $this->continueFile('header', $line);
                break;
            case self::FILE_TRAILER:
                $this->continueFile('trailer', $line);
                break;
            case self::GROUP_HEADER:
                $this->continueGroup('header', $line);
                break;
            case self::GROUP_TRAILER:
                $this->continueGroup('trailer', $line);
                break;
            case self::TRANSACTION_DETAIL:
                $this->continueTransaction($line);
                break;
        }
    }

    /**
     * Continue transaction line.
     */
    private function continueTransaction(string $line): void
    {
        if (isset($this->transactions[$this->currentTransaction]['line']) === false) {
            $this->addError($line);

            return;
        }

        $this->transactions[$this->currentTransaction]['line'] .= $line;
    }

    /**
     * Check if given code is valid.
     */
    private function isCodeValid(string $code): bool
    {
        $codes = [
            self::ACCOUNT_IDENTIFIER,
            self::ACCOUNT_TRAILER,
            self::CONTINUATION,
            self::FILE_HEADER,
            self::FILE_TRAILER,
            self::GROUP_HEADER,
            self::GROUP_TRAILER,
            self::TRANSACTION_DETAIL,
        ];

        return \in_array($code, $codes, true);
    }

    private function sanitizeFullLine(string $line): string
    {
        // Remove trailing slash
        if (Strings::endsWith($line, '/')) {
            $line = \substr($line, 0, -1);
        }

        return $line;
    }

    /**
     * Structure item content with line number.
     *
     * @return mixed[]
     */
    private function setItem(string $line): array
    {
        // Sanitise line before setting item
        return [
            'line' => \str_replace('/', '', $line),
            'line_number' => $this->currentLineNumber,
        ];
    }
}
