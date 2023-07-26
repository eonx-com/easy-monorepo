<?php

declare(strict_types=1);

namespace EonX\EasyBankFiles\Parsers\Ack;

use EonX\EasyBankFiles\Parsers\Ack\Results\PaymentAcknowledgement;

final class AbaParser extends Parser
{
    /**
     * Process line and parse data.
     */
    protected function process(): void
    {
        $result = $this->convertXmlToArray($this->contents);

        $this->acknowledgement = new PaymentAcknowledgement([
            'attributes' => $result['@attributes'] ?? null,
            'companyName' => $result['CompanyName'] ?? null,
            'customerId' => $result['CustomerId'] ?? null,
            'dateTime' => $result['DateTime'] ?? null,
            'detailedMessage' => $result['DetailedMessage'] ?? null,
            'issues' => $this->extractIssues($result['Issues']['Issue'] ?? null),
            'originalFilename' => $result['OriginalFilename'] ?? null,
            'originalMessageId' => $result['OriginalMessageId'] ?? null,
            'originalReference' => $result['OriginalReference'] ?? null,
            'paymentId' => $result['PaymentId'] ?? null,
            'userMessage' => $result['UserMessage'] ?? null,
        ]);
    }
}
