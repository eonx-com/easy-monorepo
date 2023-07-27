<?php
declare(strict_types=1);

namespace EonX\EasyBankFiles\Parsers\Ack\Results;

use DateTime;
use EonX\EasyBankFiles\Parsers\BaseResult;

/**
 * @method getAttributes()
 * @method getPaymentId()
 * @method getOriginalMessageId()
 * @method getCustomerId()
 * @method getCompanyName()
 * @method getUserMessage()
 * @method getDetailedMessage()
 * @method getOriginalFilename()
 * @method getOriginalReference()
 * @method \EonX\EasyBankFiles\Parsers\Ack\Results\Issue[] getIssues()
 */
final class PaymentAcknowledgement extends BaseResult
{
    /**
     * Convert dateTime into DateTime object.
     *
     * @return \DateTime[]|null
     *
     * @throws \Exception
     */
    public function getDateTime(): ?array
    {
        if (($this->data['dateTime']['@value'] ?? null) instanceof DateTime === false) {
            $this->data['dateTime']['@value'] = new DateTime($this->data['dateTime']['@value']);
        }

        return $this->data['dateTime'];
    }

    /**
     * Return object attributes.
     *
     * @return string[]
     */
    protected function initAttributes(): array
    {
        return [
            'attributes',
            'paymentId',
            'originalMessageId',
            'dateTime',
            'customerId',
            'companyName',
            'userMessage',
            'detailedMessage',
            'originalFilename',
            'originalReference',
            'issues',
        ];
    }
}
