<?php

declare(strict_types=1);

namespace EonX\EasyBankFiles\Parsers\Ack;

use EonX\EasyBankFiles\Parsers\Ack\Results\PaymentAcknowledgement;

final class BpbParser extends Parser
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
            'originalMessageId' => $result['MessageDetails']['OriginalMessageId'] ?? null,
            'dateTime' => $result['DateTime'] ?? null,
            'customerId' => $result['CustomerId'] ?? null,
            'companyName' => $result['CompanyName'] ?? null,
            'originalFilename' => $result['MessageDetails']['OriginalFilename'] ?? null,
            'issues' => $this->extractIssues($result['Issues']['Issue'] ?? null),
        ]);
    }
}
