<?php
declare(strict_types=1);

namespace EonX\EasyBankFiles\Parsing\Ach\ValueObject;

use EonX\EasyBankFiles\Parsing\Common\ValueObject\AbstractResult;

/**
 * @method string getAddendaTypeCode()
 * @method string getAddendaSequenceNumber()
 * @method string getCode()
 * @method string getEntryDetailSequenceNumber()
 * @method string getPaymentRelatedInformation()
 */
final class Addenda extends AbstractResult
{
    /**
     * Return object attributes.
     *
     * @return string[]
     */
    protected function initAttributes(): array
    {
        return [
            'addendaTypeCode',
            'addendaSequenceNumber',
            'code',
            'entryDetailSequenceNumber',
            'paymentRelatedInformation',
        ];
    }
}
