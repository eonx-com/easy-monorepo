<?php

declare(strict_types=1);

namespace EonX\EasyBankFiles\Parsers\Nai\Results;

/**
 * @method \EonX\EasyBankFiles\Parsers\Nai\Results\Groups\Header getHeader()
 * @method \EonX\EasyBankFiles\Parsers\Nai\Results\Groups\Trailer getTrailer()
 */
final class Group extends AbstractNaiResult
{
    /**
     * Get accounts.
     *
     * @return \EonX\EasyBankFiles\Parsers\Nai\Results\Account[]
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
