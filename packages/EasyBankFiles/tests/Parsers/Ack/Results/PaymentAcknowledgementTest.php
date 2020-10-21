<?php

declare(strict_types=1);

namespace EonX\EasyBankFiles\Tests\Parsers\Ack\Results;

use EoneoPay\Utils\DateTime;
use EonX\EasyBankFiles\Parsers\Ack\Results\PaymentAcknowledgement;
use EonX\EasyBankFiles\Tests\Parsers\TestCase;

final class PaymentAcknowledgementTest extends TestCase
{
    /**
     * Should return datetime as DateTime object
     *
     * @group Ack-PaymentAcknowledgement
     *
     * @throws \EoneoPay\Utils\Exceptions\InvalidDateTimeStringException If datetime constructor string is invalid
     */
    public function testShouldReturnDateTimeAsObject(): void
    {
        $dateString = '2017/10/17';
        $expected = [
            '@value' => new DateTime($dateString),
        ];

        $acknowledgement = new PaymentAcknowledgement([
            'dateTime' => [
                '@value' => $dateString,
            ],
        ]);

        self::assertEquals($expected, $acknowledgement->getDateTime());
    }
}
