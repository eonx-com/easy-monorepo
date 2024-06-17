<?php
declare(strict_types=1);

namespace EonX\EasyBankFiles\Tests\Unit\Parsing\Ack\Parser;

use EonX\EasyBankFiles\Parsing\Ack\Parser\AbaParser;
use EonX\EasyBankFiles\Parsing\Ack\Parser\BpbParser;
use EonX\EasyBankFiles\Parsing\Ack\ValueObject\Issue;
use EonX\EasyBankFiles\Parsing\Ack\ValueObject\PaymentAcknowledgement;
use EonX\EasyBankFiles\Parsing\Common\Converter\XmlConverter;
use EonX\EasyBankFiles\Parsing\Common\Exception\InvalidXmlException;
use EonX\EasyBankFiles\Tests\Unit\AbstractUnitTestCase;
use PHPUnit\Framework\Attributes\Group;

final class AckParserTest extends AbstractUnitTestCase
{
    /**
     * Test aba ack processing.
     */
    #[Group('Ack-Parser')]
    public function testAbaAckProcessing(): void
    {
        $content = $this->getSampleFileContents('sample.txt.ENC.PROCESSED.ACK');

        $parser = new AbaParser($content);

        $acknowledgement = new PaymentAcknowledgement([
            'attributes' => [
                'type' => 'info',
            ],
            'originalMessageId' => [
                '@value' => '94829954',
            ],
            'dateTime' => [
                '@value' => '2017/10/17',
            ],
            'customerId' => [
                '@value' => 'LOYC01AU',
            ],
            'originalFilename' => [
                '@value' => 'credit-mer_584aaa43110d77d1b224c20a20171016_221504.txt.ENC',
            ],
            'issues' => [
                new Issue([
                    'value' => 'Uploaded Interchange 95010573 for Customer 411201 and Payment Type DL_DIRECTCREDIT.',
                    'attributes' => [
                        'type' => '290049',
                    ],
                ]),
                new Issue([
                    'value' => 'Payment 105205350 successfully uploaded from a file.',
                    'attributes' => [
                        'type' => '2025',
                    ],
                ]),
                new Issue([
                    'value' => 'Payment 105205350 successfully uploaded from a file.',
                    'attributes' => [
                        'type' => '2025',
                    ],
                ]),
                new Issue([
                    'value' => 'Payment successfully validated.',
                    'attributes' => [
                        'type' => '104503',
                    ],
                ]),
                new Issue([
                    'value' => 'The Account Number 123456789 is Invalid.',
                    'attributes' => [
                        'type' => '181004',
                    ],
                ]),
                new Issue([
                    'value' => 'Credit transaction 4 created due to transaction 1 having invalid account number ' .
                        '083-163 123456789.',
                    'attributes' => [
                        'type' => '181016',
                    ],
                ]),
                new Issue([
                    'value' => 'Payment ID 105205350 has partially failed account validation.',
                    'attributes' => [
                        'type' => '181015',
                    ],
                ]),
                new Issue([
                    'value' => 'Payment is ready for authorisation - 1 authorisations required.',
                    'attributes' => [
                        'type' => '6010',
                    ],
                ]),
                new Issue([
                    'value' => 'Requested execution date changed from 16/10/2017 to 17/10/2017',
                    'attributes' => [
                        'type' => '50040',
                    ],
                ]),
                new Issue([
                    'value' => 'Payment has been fully authorised.',
                    'attributes' => [
                        'type' => '6014',
                    ],
                ]),
                new Issue([
                    'value' => 'ANDREW KALLEN [6206870316] has authorised the payment.',
                    'attributes' => [
                        'type' => '181253',
                    ],
                ]),
                new Issue([
                    'value' => 'Available funds check passed.',
                    'attributes' => [
                        'type' => '130000',
                    ],
                ]),
                new Issue([
                    'value' => 'Funds have been reserved.',
                    'attributes' => [
                        'type' => '130001',
                    ],
                ]),
                new Issue([
                    'value' => 'Payment is ready to be submitted for processing.',
                    'attributes' => [
                        'type' => '181301',
                    ],
                ]),
                new Issue([
                    'value' => 'Disbursement Report for Direct Link - Direct Credit Payment: 105205350 sent to ' .
                        'mailbox LOYC01AU',
                    'attributes' => [
                        'type' => '194500',
                    ],
                ]),
            ],
            'companyName' => [
                '@value' => 'Loyalty Corp Australia Pty Ltd',
            ],
            'paymentId' => [
                '@value' => '94829970',
            ],
            'userMessage' => [
                '@value' => 'Payment status is PROCESSED WITH INVALID TRANSACTIONS',
            ],
            'detailedMessage' => [
                '@value' => 'Payment has been successfully processed and invalid items have been returned to your ' .
                    'account.',
            ],
            'originalReference' => [
                '@value' => 'Encrypted file',
            ],
        ]);

        self::assertEquals($acknowledgement, $parser->getPaymentAcknowledgement());
    }

    /**
     * Test bpay batch ack processing.
     */
    #[Group('Ack-Parser')]
    public function testBpayBatchAckProcessing(): void
    {
        $content = $this->getSampleFileContents('bpay_batch_sample.txt.ENC.RECEIVED.ACK');

        $parser = new BpbParser($content);

        $acknowledgement = new PaymentAcknowledgement([
            'attributes' => [
                'type' => 'RECEIVED',
            ],
            'originalMessageId' => [
                '@value' => '105973228',
            ],
            'dateTime' => [
                '@value' => '2019-07-17T13:28:48+1000',
            ],
            'customerId' => [
                '@value' => 'LOYC01AU',
            ],
            'originalFilename' => [
                '@value' => '2019-07-17.bpb.ENC',
            ],
            'issues' => [
                new Issue([
                    'value' => 'BPay Batch file RECEIVED for processing',
                    'attributes' => [
                        'type' => 'RECEIVED',
                    ],
                ]),
            ],
            'companyName' => [
                '@value' => 'Loyalty Corp Australia Pty Ltd',
            ],
        ]);

        self::assertEquals($acknowledgement, $parser->getPaymentAcknowledgement());
    }

    /**
     * Test issues are correctly processed regardless of formatting.
     */
    #[Group('Ack-Parser')]
    public function testIssueProcessing(): void
    {
        $converter = new XmlConverter();

        // Test no issues
        $xml = $converter->arrayToXml([]);
        $parser = new AbaParser($xml);
        self::assertEquals([], $parser->getIssues());

        // Test single issue without attributes
        $xml = $converter->arrayToXml([
            'Issues' => [
                'Issue' => 'test',
            ],
        ]);
        $parser = new AbaParser($xml);
        self::assertEquals([
            new Issue([
                'value' => 'test',
                'attributes' => null,
            ]),
        ], $parser->getIssues());

        // Test single issue with attribute
        $xml = $converter->arrayToXml([
            'Issues' => [
                'Issue' => [
                    '@attributes' => [
                        'id' => '10',
                    ],
                    '@value' => 'test',
                ],
            ],
        ]);
        $parser = new AbaParser($xml);
        self::assertEquals(
            [
                new Issue([
                    'value' => 'test',
                    'attributes' => [
                        'id' => '10',
                    ],
                ]),
            ],
            $parser->getIssues()
        );

        // Test array of issues
        $xml = $converter->arrayToXml([
            'Issues' => [
                'Issue' => [
                    [
                        '@attributes' => [
                            'id' => '10',
                        ],
                        '@value' => 'test',
                    ],
                    [
                        '@attributes' => [
                            'id' => '11',
                        ],
                        '@value' => 'test2',
                    ],
                ],
            ],
        ]);
        $parser = new AbaParser($xml);
        self::assertEquals(
            [
                new Issue([
                    'value' => 'test',
                    'attributes' => [
                        'id' => '10',
                    ],
                ]),
                new Issue([
                    'value' => 'test2',
                    'attributes' => [
                        'id' => '11',
                    ],
                ]),
            ],
            $parser->getIssues()
        );
    }

    /**
     * Tests that the parser successfully mitigates a raised exception around invalid content.
     */
    public function testParserMitigatesInvalidData(): void
    {
        $content = $this->getSampleFileContents('invalid_node_value_sample.txt.ENC.PENDING.ACK');

        $parser = new AbaParser($content);

        self::assertInstanceOf(Issue::class, $parser->getIssues()[0]);
        self::assertIsArray($parser->getIssues()[0]->getAttributes());
    }

    /**
     * Tests that the parser still throws the raised exception if the value back from the mitigator is empty.
     */
    public function testParserStillThrowsExceptionIfMitigationReturnsEmpty(): void
    {
        $xml = <<<'XML'

XML;

        $this->expectException(InvalidXmlException::class);

        new AbaParser($xml);
    }

    /**
     * Should return empty collection if no issue
     * and an empty array if attribute is not found in the xml.
     */
    #[Group('Ack-Parser')]
    public function testShouldReturnIfNoIssues(): void
    {
        $content = $this->getSampleFileContents('no_issues_sample.txt.ENC.PROCESSED.ACK');

        $parser = new AbaParser($content);

        self::assertEquals([], $parser->getIssues());

        // PaymentId is not in the xml
        self::assertNull($parser->getPaymentAcknowledgement()->getPaymentId());
    }

    /**
     * Should return PaymentAcknowledgement, Issues collection and Issue object.
     */
    #[Group('Ack-Parser')]
    public function testShouldReturnIssues(): void
    {
        $content = $this->getSampleFileContents('sample.txt.ENC.PROCESSED.ACK');

        $parser = new AbaParser($content);

        self::assertInstanceOf(Issue::class, $parser->getIssues()[0]);
        self::assertIsArray($parser->getIssues()[0]->getAttributes());

        self::assertInstanceOf(Issue::class, $parser->getPaymentAcknowledgement()->getIssues()[0]);
        self::assertIsArray($parser->getPaymentAcknowledgement()->getIssues()[0]->getAttributes());
    }

    private function getSampleFileContents(string $file): string
    {
        return \file_get_contents(
            \realpath(__DIR__) . '/../../../../../Fixture/Parsing/Ack/' . $file
        ) ?: '';
    }
}
