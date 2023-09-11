<?php
declare(strict_types=1);

namespace EonX\EasyBankFiles\Parsers\Ack;

use EonX\EasyBankFiles\Exceptions\InvalidXmlException;
use EonX\EasyBankFiles\Helpers\XmlConverter;
use EonX\EasyBankFiles\Helpers\XmlFailureMitigation;
use EonX\EasyBankFiles\Parsers\Ack\Results\Issue;
use EonX\EasyBankFiles\Parsers\Ack\Results\PaymentAcknowledgement;
use EonX\EasyBankFiles\Parsers\BaseParser;

abstract class Parser extends BaseParser
{
    protected PaymentAcknowledgement $acknowledgement;

    public function __construct(string $contents)
    {
        parent::__construct($contents);

        $this->process();
    }

    /**
     * @return \EonX\EasyBankFiles\Parsers\Ack\Results\Issue[]
     */
    public function getIssues(): array
    {
        return $this->acknowledgement->getIssues();
    }

    /**
     * Return PaymentAcknowledgement.
     */
    public function getPaymentAcknowledgement(): PaymentAcknowledgement
    {
        return $this->acknowledgement;
    }

    /**
     * Attempts to convert the provided XML string to an array.
     */
    protected function convertXmlToArray(string $xml): array
    {
        $xmlConverter = new XmlConverter();

        try {
            $result = $xmlConverter->xmlToArray($xml, XmlConverter::XML_INCLUDE_ATTRIBUTES);
        } catch (InvalidXmlException $exception) {
            // When an exception is thrown, let's attempt to mitigate the issue by cleaning up some common
            // inconsistencies from the bank's side
            $fixedContents = XmlFailureMitigation::tryMitigateParseFailures($xml);

            // If the content back from mitigation is empty, throw the initial exception
            if ($fixedContents === '') {
                throw $exception;
            }

            // Run the converter again, this time not capturing any exceptions
            $result = $xmlConverter->xmlToArray($fixedContents, XmlConverter::XML_INCLUDE_ATTRIBUTES);
        }

        return $result;
    }

    /**
     * Determine how to process issues, this array can change depending on whether there
     * are one or many issues to be stored.
     *
     * @return \EonX\EasyBankFiles\Parsers\Ack\Results\Issue[]
     */
    protected function extractIssues(mixed $issues): array
    {
        // If there are no issues, return
        if ($issues === null) {
            return [];
        }

        // If issues is a single item, force to array
        if (\array_key_exists('@value', $issues)) {
            $issues = [$issues];
        }

        // Process issues array
        $objects = [];
        foreach ($issues as $issue) {
            $objects[] = new Issue([
                'attributes' => $issue['@attributes'] ?? null,
                'value' => $issue['@value'] ?? null,
            ]);
        }

        return $objects;
    }
}
