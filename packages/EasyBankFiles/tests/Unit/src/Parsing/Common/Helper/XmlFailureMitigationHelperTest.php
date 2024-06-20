<?php
declare(strict_types=1);

namespace EonX\EasyBankFiles\Tests\Unit\Parsing\Common\Helper;

use EonX\EasyBankFiles\Parsing\Common\Helper\XmlFailureMitigationHelper;
use EonX\EasyBankFiles\Tests\Unit\AbstractUnitTestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;

#[CoversClass(XmlFailureMitigationHelper::class)]
final class XmlFailureMitigationHelperTest extends AbstractUnitTestCase
{
    /**
     * Gets the XML scenarios for testing.
     *
     * @see testMitigationReplacesInvalidLines
     */
    public static function getXmlScenarios(): iterable
    {
        yield 'HTML-like characters in node value' => [
            'input' => '
<PaymentsAcknowledgement type="warning">
    <Issues>
        <Issue type="test">This is a very <1> important <test> issue.</Issue>
    </Issues>
</PaymentsAcknowledgement>',
            'expected' => '
<PaymentsAcknowledgement type="warning">
    <Issues>
        <Issue type="test">This is a very &lt;1&gt; important &lt;test&gt; issue.</Issue>
    </Issues>
</PaymentsAcknowledgement>',
        ];
    }

    /**
     * Test that the helper class does not touch valid XML.
     */
    public function testMitigationLeavesValidXmlAlone(): void
    {
        // phpcs:disable
        // Disabled to ignore long lines in XML sample
        $xml = <<<'XML'
<PaymentsAcknowledgement type="info">
<PaymentId>94829970</PaymentId>
<OriginalMessageId>94829954</OriginalMessageId>
<DateTime>2017/10/17</DateTime>
<CustomerId>LOYC01AU</CustomerId>
<CompanyName>Loyalty Corp Australia Pty Ltd</CompanyName>
<UserMessage>Payment status is PROCESSED WITH INVALID TRANSACTIONS</UserMessage>
<DetailedMessage>
    Payment has been successfully processed and invalid items have been returned to your account.
</DetailedMessage>
<OriginalFilename>credit-mer_584aaa43110d77d1b224c20a20171016_221504.txt.ENC</OriginalFilename>
<OriginalReference>Encrypted file</OriginalReference>
<Issues>
<Issue type="2025">Payment 105205350 successfully uploaded from a file.</Issue>
<Issue type="2025">Payment 105205350 successfully uploaded from a file.</Issue>
<Issue type="104503">Payment successfully validated.</Issue>
<Issue type="181301">Payment is ready to be submitted for processing.</Issue>
</Issues>
</PaymentsAcknowledgement>
XML;
        // phpcs:enable

        $result = XmlFailureMitigationHelper::tryMitigateParseFailures($xml);

        self::assertSame($xml, $result);
    }

    /**
     * Tests that the helper method successfully handles the provided scenarios.
     */
    #[DataProvider('getXmlScenarios')]
    public function testMitigationReplacesInvalidLines(string $input, string $expected): void
    {
        $result = XmlFailureMitigationHelper::tryMitigateParseFailures(\trim($input));

        self::assertSame(\trim($expected), $result);
    }
}
