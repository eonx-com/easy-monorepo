<?php
declare(strict_types=1);

namespace EonX\EasyBankFiles\Tests\Parsers\Ack\Results;

use DateTime;
use EonX\EasyBankFiles\Parsers\Ack\Results\PaymentAcknowledgement;
use EonX\EasyBankFiles\Tests\Parsers\TestCase;
use PHPUnit\Framework\Attributes\Group;

final class PaymentAcknowledgementTest extends TestCase
{
    /**
     * Should return datetime as DateTime object.
     */
    #[Group('Ack-PaymentAcknowledgement')]
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
