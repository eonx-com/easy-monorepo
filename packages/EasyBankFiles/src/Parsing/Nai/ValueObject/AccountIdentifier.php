<?php
declare(strict_types=1);

namespace EonX\EasyBankFiles\Parsing\Nai\ValueObject;

use EonX\EasyBankFiles\Parsing\Common\ValueObject\AbstractResult;

/**
 * @method string getCode()
 * @method string getCommercialAccountNumber()
 * @method string getCurrencyCode()
 * @method array getTransactionCodes()
 */
final class AccountIdentifier extends AbstractResult
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
