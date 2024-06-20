<?php
declare(strict_types=1);

namespace EonX\EasyBankFiles\Tests\Unit\Parsing\Nai\ValueObject;

use EonX\EasyBankFiles\Parsing\Nai\ValueObject\FileTrailer;
use EonX\EasyBankFiles\Tests\Unit\AbstractUnitTestCase;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(FileTrailer::class)]
final class FileTrailerTest extends AbstractUnitTestCase
{
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
}
