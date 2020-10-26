<?php

declare(strict_types=1);

namespace EonX\EasyBankFiles\Parsers\Ack;

use EonX\EasyBankFiles\Parsers\Ack\Results\PaymentAcknowledgement;

final class AbaParser extends Parser
{
    /**
     * Process line and parse data.
     *
     * @throws \EoneoPay\Utils\Exceptions\InvalidXmlException
     */
    protected function process(): void
    {
        $result = $this->convertXmlToArray($this->contents);

        $this->acknowledgement = new PaymentAcknowledgement([
            'attributes' => $result['@attributes'] ?? null,
            'paymentId' => $result['PaymentId'] ?? null,
            'originalMessageId' => $result['OriginalMessageId'] ?? null,
            'dateTime' => $result['DateTime'] ?? null,
            'customerId' => $result['CustomerId'] ?? null,
            'companyName' => $result['CompanyName'] ?? null,
            'userMessage' => $result['UserMessage'] ?? null,
            'detailedMessage' => $result['DetailedMessage'] ?? null,
            'originalFilename' => $result['OriginalFilename'] ?? null,
            'originalReference' => $result['OriginalReference'] ?? null,
            'issues' => $this->extractIssues($result['Issues']['Issue'] ?? null),
        ]);
    }
}
