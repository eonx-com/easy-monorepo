<?php
declare(strict_types=1);

namespace EonX\EasyBankFiles\Tests\Unit\Generation\Bpay\Generator;

use EonX\EasyBankFiles\Generation\Bpay\Generator\BpayGenerator;
use EonX\EasyBankFiles\Generation\Bpay\ValueObject\Header;
use EonX\EasyBankFiles\Generation\Bpay\ValueObject\Transaction;
use EonX\EasyBankFiles\Generation\Common\Exception\InvalidArgumentException;
use EonX\EasyBankFiles\Generation\Common\Generator\GeneratorInterface;
use EonX\EasyBankFiles\Tests\Unit\AbstractUnitTestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Group;

#[CoversClass(BpayGenerator::class)]
final class BpayGeneratorTest extends AbstractUnitTestCase
{
    /**
     * Generator should throw exception when no transactions given.
     *
     * @throws \EonX\EasyBankFiles\Generation\Common\Exception\InvalidArgumentException
     */
    public function testEmptyTransactionsException(): void
    {
        $this->expectException(InvalidArgumentException::class);

        new BpayGenerator($this->createHeader(), []);
    }

    /**
     * Generated data should be present in the content.
     *
     * @throws \EonX\EasyBankFiles\Generation\Common\Exception\InvalidArgumentException
     */
    #[Group('Generator-Bpay')]
    public function testGeneratedDataShouldBeInTheContent(): void
    {
        $header = $this->createHeader();

        // Create a transaction and set it's values
        $trans1 = $this->createTransaction();
        $trans1
            ->setAttribute('billerCode', '11133')
            ->setAttribute('amount', '200');
        $trans2 = $this->createTransaction();

        $generator = new BpayGenerator($header, [$trans1, $trans2]);

        self::assertStringContainsString($header->getAttributesAsLine(), $generator->getContents());
        self::assertStringContainsString($trans1->getAttributesAsLine(), $generator->getContents());
    }

    /**
     * Generator should throw exception when invalid transaction given.
     *
     * @throws \EonX\EasyBankFiles\Generation\Common\Exception\InvalidArgumentException
     */
    public function testInvalidTransactionException(): void
    {
        $this->expectException(InvalidArgumentException::class);

        (new BpayGenerator($this->createHeader(), ['invalid']))
            ->setBreakLines(GeneratorInterface::BREAK_LINE_WINDOWS)
            ->getContents();
    }

    /**
     * Create a Header object.
     */
    protected function createHeader(): Header
    {
        return new Header([
            'batchCustomerId' => '85765',
            'customerShortName' => 'CustomerShortName',
            'processingDate' => '20171104',
        ]);
    }

    /**
     * Create a Transaction object.
     */
    protected function createTransaction(): Transaction
    {
        return new Transaction([
            'billerCode' => '5566778',
            'paymentAccountBSB' => '084455',
            'paymentAccountNumber' => '112233445',
            'customerReferenceNumber' => '9457689335',
            'amount' => '2599',
            'lodgementReference1' => 'lodgeRef1',
            'lodgementReference2' => 'lodgeRef2',
            'lodgementReference3' => 'lodgeRef2',
        ]);
    }
}
