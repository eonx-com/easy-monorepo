<?php
declare(strict_types=1);

namespace EonX\EasyBankFiles\Parsing\Ack\Parser;

use EonX\EasyBankFiles\Parsing\Ack\ValueObject\PaymentAcknowledgement;

final class BpbParser extends AbstractAckParser
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
            'issues' => $this->extractIssues($result['Issues']['Issue'] ?? null),
            'originalFilename' => $result['MessageDetails']['OriginalFilename'] ?? null,
            'originalMessageId' => $result['MessageDetails']['OriginalMessageId'] ?? null,
        ]);
    }
}
