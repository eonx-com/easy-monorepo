<?php
declare(strict_types=1);

namespace EonX\EasyBankFiles\Parsing\Nai\ValueObject;

/**
 * @method GroupHeader getHeader()
 * @method GroupTrailer getTrailer()
 */
final class Group extends AbstractNaiResult
{
    /**
     * Get accounts.
     *
     * @return \EonX\EasyBankFiles\Parsing\Nai\ValueObject\Account[]
     */
    public function getAccounts(): array
    {
        return $this->context->getAccountsForGroup($this->data['index']);
    }

    /**
     * Get file.
     */
    public function getFile(): ?File
    {
        return $this->context->getFile();
    }

    /**
     * Return object attributes.
     *
     * @return string[]
     */
    protected function initAttributes(): array
    {
        return ['header', 'index', 'trailer'];
    }
}
