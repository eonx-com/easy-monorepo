<?php
declare(strict_types=1);

namespace EonX\EasyBankFiles\Parsing\Ach\ValueObject;

use EonX\EasyBankFiles\Parsing\Common\ValueObject\AbstractResult;

/**
 * @method string getBatchNumber()
 * @method string getCode()
 * @method string getCompanyDiscretionaryData()
 * @method string getCompanyDescriptiveDate()
 * @method string getCompanyEntryDescription()
 * @method string getCompanyIdentification()
 * @method string getCompanyName()
 * @method string getEffectiveEntryDate()
 * @method string getOriginatingDfiIdentification()
 * @method string getOriginatorStatusCode()
 * @method string getServiceClassCode()
 * @method string getSettlementDate()
 * @method string getStandardEntryClassCode()
 */
final class BatchHeader extends AbstractResult
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
            'companyDiscretionaryData',
            'companyDescriptiveDate',
            'companyEntryDescription',
            'companyIdentification',
            'companyName',
            'effectiveEntryDate',
            'originatingDfiIdentification',
            'originatorStatusCode',
            'serviceClassCode',
            'settlementDate',
            'standardEntryClassCode',
        ];
    }
}
