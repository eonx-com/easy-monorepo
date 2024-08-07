<?php
declare(strict_types=1);

namespace EonX\EasyBankFiles\Tests\Unit\Parsing\BpayBatch\ValueObject;

use DateTime;
use EonX\EasyBankFiles\Parsing\BpayBatch\ValueObject\HeaderRecord;
use EonX\EasyBankFiles\Tests\Unit\AbstractUnitTestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Group;

#[CoversClass(HeaderRecord::class)]
final class HeaderRecordTest extends AbstractUnitTestCase
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
     * Should return processing date as a null when date string is invalid.
     */
    #[DataProvider('provideInvalidDateProcessedValues')]
    #[Group('Batch-Header')]
    public function testGetDateProcessedShouldReturnNull(array $dateProcessed): void
    {
        $header = new HeaderRecord($dateProcessed);

        self::assertNull($header->getDateProcessedObject());
    }

    /**
     * Should return date as an DateTime object.
     */
    #[Group('Batch-Header')]
    public function testShouldReturnDateProcessedObject(): void
    {
        $header = new HeaderRecord([
            'dateProcessed' => '20190919',
        ]);

        /** @var \DateTime $object */
        $object = $header->getDateProcessedObject();
        self::assertInstanceOf(DateTime::class, $object);
        self::assertSame('19-09-2019', $object->format('d-m-Y'));
    }
}
