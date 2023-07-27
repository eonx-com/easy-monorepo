<?php
declare(strict_types=1);

namespace EonX\EasyBankFiles\Parsers\Bpay\Batch\Results;

use DateTime;
use DateTimeInterface;
use EonX\EasyBankFiles\Parsers\BaseResult;

/**
 * @method string|null getCustomerId()
 * @method string|null getCustomerShortName()
 * @method string|null getDateProcessed()
 * @method string|null getRestOfRecord()
 */
final class Header extends BaseResult
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
