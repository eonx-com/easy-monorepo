<?php

declare(strict_types=1);

namespace EonX\EasyBankFiles\Parsers\DirectEntry\Results;

use EonX\EasyBankFiles\Parsers\BaseResult;
use EonX\EasyBankFiles\Parsers\DirectEntry\HasAmounts;

/**
 * @method string|null getBsb()
 * @method string|null getNumberPayments()
 */
final class Trailer extends BaseResult
{
    use HasAmounts;

    /**
     * Get total credit amount as in file trailer as dollars
     */
    public function getTotalCreditAmount(): string
    {
        return $this->createAmount($this->data['totalCreditAmount'] ?? '');
    }

    /**
     * Get total debit amount as in file trailer as dollars
     */
    public function getTotalDebitAmount(): string
    {
        return $this->createAmount($this->data['totalDebitAmount'] ?? '');
    }

    /**
     * Get total net amount as in file trailer as dollars
     */
    public function getTotalNetAmount(): string
    {
        return $this->createAmount($this->data['totalNetAmount'] ?? '');
    }

    /**
     * Return object attributes.
     *
     * @return string[]
     */
    protected function initAttributes(): array
    {
        return ['bsb', 'numberPayments', 'totalNetAmount', 'totalCreditAmount', 'totalDebitAmount'];
    }
}
