<?php
declare(strict_types=1);

namespace EonX\EasyBankFiles\Tests\Unit\Parsing\Nai\ValueObject;

use EonX\EasyBankFiles\Parsing\Nai\ValueObject\FileTrailer;
use EonX\EasyBankFiles\Tests\Unit\AbstractUnitTestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;

#[CoversClass(FileTrailer::class)]
final class FileTrailerTest extends AbstractUnitTestCase
{
    /**
     * @see testFormatsControlTotalCents
     */
    public static function provideControlTotalCents(): iterable
    {
        yield 'zero cents' => ['onFile' => '10000', 'expected' => 100.00];
        yield 'one cent' => ['onFile' => '10001', 'expected' => 100.01];
        yield 'five cents' => ['onFile' => '10005', 'expected' => 100.05];
        yield 'nine cents' => ['onFile' => '10009', 'expected' => 100.09];
        yield 'ten cents' => ['onFile' => '10010', 'expected' => 100.10];
        yield 'ninety-nine cents' => ['onFile' => '10099', 'expected' => 100.99];
    }

    /**
     * The last two digits of a control total are the cents and must be preserved exactly,
     * including a leading zero (cents 01-09).
     */
    #[DataProvider('provideControlTotalCents')]
    public function testFormatsControlTotalCents(string $onFile, float $expected): void
    {
        $trailer = new FileTrailer([
            'fileControlTotalA' => $onFile,
            'fileControlTotalB' => $onFile,
        ]);

        self::assertSame($expected, $trailer->getFileControlTotalA());
        self::assertSame($expected, $trailer->getFileControlTotalB());
    }

    /**
     * Result should return data as expected.
     */
    public function testGetDataAsExpected(): void
    {
        $data = [
            'code' => '03',
            'fileControlTotalA' => '10000',
            'fileControlTotalB' => '10000',
            'numberOfGroups' => '3',
            'numberOfRecords' => '4',
        ];

        $trailer = new FileTrailer($data);

        self::assertSame($data['code'], $trailer->getCode());
        self::assertSame((float)100, $trailer->getFileControlTotalA());
        self::assertSame((float)100, $trailer->getFileControlTotalB());
        self::assertSame($data['numberOfGroups'], $trailer->getNumberOfGroups());
        self::assertSame($data['numberOfRecords'], $trailer->getNumberOfRecords());
    }

    /**
     * Two genuinely different control totals (e.g. $100.01 vs $100.10) must not collapse to the
     * same value, otherwise an equality-based reconciliation check cannot tell them apart.
     */
    public function testDistinctCentsDoNotCollide(): void
    {
        $oneCent = (new FileTrailer(['fileControlTotalA' => '10001']))->getFileControlTotalA();
        $tenCents = (new FileTrailer(['fileControlTotalA' => '10010']))->getFileControlTotalA();

        self::assertNotSame($oneCent, $tenCents);
    }
}
