<?php
declare(strict_types=1);

namespace EonX\EasyBankFiles\Tests\Parsers\DirectEntry\Results;

use DateTime;
use EonX\EasyBankFiles\Parsers\DirectEntry\Results\Header;
use EonX\EasyBankFiles\Tests\Parsers\TestCase;

/**
 * @covers \EonX\EasyBankFiles\Parsers\DirectEntry\Results\Header
 */
final class HeaderTest extends TestCase
{
    /**
     * @see testGetDateProcessedShouldReturnNull
     */
    public static function provideInvalidDateProcessedValues(): iterable
    {
        yield 'null dateProcessed' => [
            'dateProcessed' => [
                'dateProcessed' => null,
            ],
        ];
        yield 'dateProcessed has non-digital symbols' => [
            'dateProcessed' => [
                'dateProcessed' => '201909ab',
            ],
        ];
    }

    /**
     * Test if date conversion works as expected.
     */
    public function testDateConversion(): void
    {
        $header = new Header([
            'dateProcessed' => '070904',
        ]);

        $expectedDateTime = new DateTime('2004-09-07');

        self::assertEquals($expectedDateTime, $header->getDateProcessedObject());
    }

    /**
     * Should return processing date as a null when date string is invalid.
     *
     * @dataProvider provideInvalidDateProcessedValues
     */
    public function testGetDateProcessedShouldReturnNull(array $dateProcessed): void
    {
        $header = new Header($dateProcessed);

        self::assertNull($header->getDateProcessedObject());
    }
}
