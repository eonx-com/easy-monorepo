<?php
declare(strict_types=1);

namespace EonX\EasyBankFiles\Tests\Generators\Bpay;

use EonX\EasyBankFiles\Generators\Bpay\Generator;
use EonX\EasyBankFiles\Generators\Exceptions\InvalidArgumentException;
use EonX\EasyBankFiles\Generators\Interfaces\GeneratorInterface;

final class GeneratorTest extends TestCase
{
    /**
     * Generator should throw exception when no transactions given.
     *
     * @throws \EonX\EasyBankFiles\Generators\Exceptions\InvalidArgumentException
     */
    public function testEmptyTransactionsException(): void
    {
        $this->expectException(InvalidArgumentException::class);

        new Generator($this->createHeader(), []);
    }

    /**
     * Generated data should be present in the content.
     *
     * @throws \EonX\EasyBankFiles\Generators\Exceptions\InvalidArgumentException
     *
     * @group Generator-Bpay
     */
    public function testGeneratedDataShouldBeInTheContent(): void
    {
        $header = $this->createHeader();

        // Create a transaction and set it's values
        $trans1 = $this->createTransaction();
        $trans1
            ->setAttribute('billerCode', '11133')
            ->setAttribute('amount', '200');
        $trans2 = $this->createTransaction();

        $generator = new Generator($header, [$trans1, $trans2]);

        self::assertStringContainsString($header->getAttributesAsLine(), $generator->getContents());
        self::assertStringContainsString($trans1->getAttributesAsLine(), $generator->getContents());
    }

    /**
     * Generator should throw exception when invalid transaction given.
     *
     * @throws \EonX\EasyBankFiles\Generators\Exceptions\InvalidArgumentException
     */
    public function testInvalidTransactionException(): void
    {
        $this->expectException(InvalidArgumentException::class);

        (new Generator($this->createHeader(), ['invalid']))
            ->setBreakLines(GeneratorInterface::BREAK_LINE_WINDOWS)
            ->getContents();
    }
}
