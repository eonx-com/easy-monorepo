<?php
declare(strict_types=1);

namespace EonX\EasyBankFiles\Parsing\Ack\ValueObject;

use DateTime;
use EonX\EasyBankFiles\Parsing\Common\ValueObject\AbstractResult;

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
 * @method Issue[] getIssues()
 */
final class PaymentAcknowledgement extends AbstractResult
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
