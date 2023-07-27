<?php
declare(strict_types=1);

namespace EonX\EasyBankFiles\Tests\Generators\Bpay;

use EonX\EasyBankFiles\Generators\Bpay\Objects\Header;
use EonX\EasyBankFiles\Generators\Bpay\Objects\Transaction;
use EonX\EasyBankFiles\Tests\Generators\TestCase as GeneratorTestCase;

/**
 * @covers \EonX\EasyBankFiles\Tests\Generators\Bpay\TestCase
 */
abstract class TestCase extends GeneratorTestCase
{
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
