<?php
declare(strict_types=1);

namespace EonX\EasyBankFiles\Parsing\Nai\ValueObject;

/**
 * @method \EonX\EasyBankFiles\Parsing\Nai\ValueObject\AccountIdentifier getIdentifier()
 * @method \EonX\EasyBankFiles\Parsing\Nai\ValueObject\AccountTrailer getTrailer()
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
     * @return \EonX\EasyBankFiles\Parsing\Nai\ValueObject\Transaction[]
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
