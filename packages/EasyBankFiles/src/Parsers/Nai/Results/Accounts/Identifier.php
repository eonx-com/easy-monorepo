<?php

declare(strict_types=1);

namespace EonX\EasyBankFiles\Parsers\Nai\Results\Accounts;

use EonX\EasyBankFiles\Parsers\BaseResult;

/**
 * @method string getCode()
 * @method string getCommercialAccountNumber()
 * @method string getCurrencyCode()
 * @method array getTransactionCodes()
 */
final class Identifier extends BaseResult
{
    /**
     * Return object attributes.
     *
     * @return string[]
     */
    protected function initAttributes(): array
    {
        return ['code', 'commercialAccountNumber', 'currencyCode', 'transactionCodes'];
    }
}
