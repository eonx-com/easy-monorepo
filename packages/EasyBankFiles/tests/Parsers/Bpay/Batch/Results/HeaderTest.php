<?php
declare(strict_types=1);

namespace EonX\EasyBankFiles\Tests\Parsers\Bpay\Batch\Results;

use DateTime;
use EonX\EasyBankFiles\Parsers\Bpay\Batch\Results\Header;
use EonX\EasyBankFiles\Tests\Parsers\TestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Group;

#[CoversClass(Header::class)]
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
     * Should return processing date as a null when date string is invalid.
     */
    #[DataProvider('provideInvalidDateProcessedValues')]
    #[Group('Batch-Header')]
    public function testGetDateProcessedShouldReturnNull(array $dateProcessed): void
    {
        $header = new Header($dateProcessed);

        self::assertNull($header->getDateProcessedObject());
    }

    /**
     * Should return date as an DateTime object.
     */
    #[Group('Batch-Header')]
    public function testShouldReturnDateProcessedObject(): void
    {
        $header = new Header([
            'dateProcessed' => '20190919',
        ]);

        /** @var \DateTime $object */
        $object = $header->getDateProcessedObject();
        self::assertInstanceOf(DateTime::class, $object);
        self::assertSame('19-09-2019', $object->format('d-m-Y'));
    }
}
