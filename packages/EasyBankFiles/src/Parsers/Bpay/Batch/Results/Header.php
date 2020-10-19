<?php

declare(strict_types=1);

namespace EonX\EasyBankFiles\Parsers\Bpay\Batch\Results;

use DateTime;
use DateTimeInterface;
use EonX\EasyBankFiles\Parsers\BaseResult;

/**
 * @method string|null getCustomerId()
 * @method string|null getCustomerShortName()
 * @method string|null getProcessingDate()
 * @method string|null getRestOfRecord()
 */
final class Header extends BaseResult
{
    public function getProcessingDateObject(): ?DateTimeInterface
    {
        $value = $this->data['processingDate'] ?? null;

        if ($value === null) {
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
        return ['customerId', 'customerShortName', 'processingDate', 'restOfRecord'];
    }
}
