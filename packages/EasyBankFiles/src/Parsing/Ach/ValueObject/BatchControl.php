<?php
declare(strict_types=1);

namespace EonX\EasyBankFiles\Parsing\Ach\ValueObject;

use EonX\EasyBankFiles\Parsing\Common\ValueObject\AbstractResult;

/**
 * @method string getBatchNumber()
 * @method string getCode()
 * @method string getCompanyIdentification()
 * @method string getEntryAddendaCount()
 * @method string getEntryHash()
 * @method string getMessageAuthenticationCode()
 * @method string getOriginatingDfiIdentification()
 * @method string getReserved()
 * @method string getServiceClassCode()
 * @method string getTotalCreditAmount()
 * @method string getTotalDebitAmount()
 */
final class BatchControl extends AbstractResult
{
    /**
     * Return object attributes.
     *
     * @return string[]
     */
    protected function initAttributes(): array
    {
        return [
            'batchNumber',
            'code',
            'companyIdentification',
            'entryAddendaCount',
            'entryHash',
            'messageAuthenticationCode',
            'originatingDfiIdentification',
            'reserved',
            'serviceClassCode',
            'totalCreditAmount',
            'totalDebitAmount',
        ];
    }
}
