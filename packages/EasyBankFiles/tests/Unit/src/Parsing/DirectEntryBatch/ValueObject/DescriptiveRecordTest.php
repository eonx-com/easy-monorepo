<?php
declare(strict_types=1);

namespace EonX\EasyBankFiles\Tests\Unit\Parsing\DirectEntryBatch\ValueObject;

use DateTime;
use EonX\EasyBankFiles\Parsing\DirectEntryBatch\ValueObject\DescriptiveRecord;
use EonX\EasyBankFiles\Tests\Unit\AbstractUnitTestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;

#[CoversClass(DescriptiveRecord::class)]
final class DescriptiveRecordTest extends AbstractUnitTestCase
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
        $header = new DescriptiveRecord([
            'dateProcessed' => '070904',
        ]);

        $expectedDateTime = new DateTime('2004-09-07');

        self::assertEquals($expectedDateTime, $header->getDateProcessedObject());
    }

    /**
     * Should return processing date as a null when date string is invalid.
     */
    #[DataProvider('provideInvalidDateProcessedValues')]
    public function testGetDateProcessedShouldReturnNull(array $dateProcessed): void
    {
        $header = new DescriptiveRecord($dateProcessed);

        $result = $header->getDateProcessedObject();

        self::assertNull($result);
    }
}
