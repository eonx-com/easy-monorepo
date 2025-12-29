<?php
declare(strict_types=1);

namespace EonX\EasyBankFiles\Parsing\BpayBatch\ValueObject;

use DateTime;
use DateTimeInterface;
use EonX\EasyBankFiles\Parsing\Common\ValueObject\AbstractResult;

/**
 * @method string getCustomerId()
 * @method string getCustomerShortName()
 * @method string getDateProcessed()
 * @method string getRestOfRecord()
 */
final class HeaderRecord extends AbstractResult
{
    /**
     * Return processed date as a DateTime object.
     */
    public function getDateProcessedObject(): ?DateTimeInterface
    {
        $value = $this->data['dateProcessed'];

        if (
            \is_string($value) === false ||
            \ctype_digit($value) === false
        ) {
            return null;
        }

        return new DateTime($value);
    }

    /**
     * Return object attributes.
     *
     * @return string[]
     */
    protected function initAttributes(): array
    {
        return ['customerId', 'customerShortName', 'dateProcessed', 'restOfRecord'];
    }
}
