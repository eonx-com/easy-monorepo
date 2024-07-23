<?php
declare(strict_types=1);

namespace EonX\EasyBankFiles\Tests\Unit\Parsing\Ack\ValueObject;

use DateTime;
use EonX\EasyBankFiles\Parsing\Ack\ValueObject\PaymentAcknowledgement;
use EonX\EasyBankFiles\Tests\Unit\AbstractUnitTestCase;
use PHPUnit\Framework\Attributes\Group;

final class PaymentAcknowledgementTest extends AbstractUnitTestCase
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
