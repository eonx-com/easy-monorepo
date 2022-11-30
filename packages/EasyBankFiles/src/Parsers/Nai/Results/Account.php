<?php

declare(strict_types=1);

namespace EonX\EasyBankFiles\Parsers\Nai\Results;

/**
 * @method \EonX\EasyBankFiles\Parsers\Nai\Results\Accounts\Identifier getIdentifier()
 * @method \EonX\EasyBankFiles\Parsers\Nai\Results\Accounts\Trailer getTrailer()
 */
final class Account extends AbstractNaiResult
{
    /**
     * Get group.
     */
    public function getGroup(): ?Group
    {
        return $this->context->getGroup($this->data['group']);
    }

    /**
     * Get transactions.
     *
     * @return \EonX\EasyBankFiles\Parsers\Nai\Results\Transaction[]
     */
    public function getTransactions(): array
    {
        return $this->context->getTransactionsForAccount($this->data['index']);
    }

    /**
     * Return object attributes.
     *
     * @return string[]
     */
    protected function initAttributes(): array
    {
        return ['group', 'identifier', 'index', 'trailer'];
    }
}
