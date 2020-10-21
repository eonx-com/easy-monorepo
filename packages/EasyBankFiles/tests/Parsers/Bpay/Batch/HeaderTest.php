<?php

declare(strict_types=1);

namespace EonX\EasyBankFiles\Tests\Parsers\Bpay\Batch;

use DateTime;
use EonX\EasyBankFiles\Parsers\Bpay\Batch\Results\Header;
use EonX\EasyBankFiles\Tests\Parsers\TestCase;

final class HeaderTest extends TestCase
{
    /**
     * Should return dates as an object
     *
     * @group Batch-Header
     *
     * @throws \EoneoPay\Utils\Exceptions\InvalidDateTimeStringException If datetime constructor string is invalid
     */
    public function testShouldReturnProcessingDateObject(): void
    {
        $header = new Header([
            'processingDate' => '20190919',
        ]);

        $object = $header->getProcessingDateObject();
        self::assertInstanceOf(DateTime::class, $object);
        self::assertSame('19-09-2019', ($object instanceof DateTime) === true ? $object->format('d-m-Y') : '');
    }

    /**
     * Should return processing date as a null
     *
     * @group Batch-Trailer
     *
     * @throws \EoneoPay\Utils\Exceptions\InvalidDateTimeStringException If datetime constructor string is invalid
     */
    public function testShouldReturnProcessingDateObjectNull(): void
    {
        $header = new Header();

        self::assertNull($header->getProcessingDateObject());
    }
}
