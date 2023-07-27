<?php
declare(strict_types=1);

namespace EonX\EasyBankFiles\Tests\Parsers\Nai\Results\Files;

use EonX\EasyBankFiles\Parsers\Nai\Results\Files\Trailer;
use EonX\EasyBankFiles\Tests\Parsers\TestCase;

/**
 * @covers \EonX\EasyBankFiles\Parsers\Nai\Results\Files\Trailer
 */
final class TrailerTest extends TestCase
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

        $trailer = new Trailer($data);

        self::assertSame($data['code'], $trailer->getCode());
        self::assertSame((float)100, $trailer->getFileControlTotalA());
        self::assertSame((float)100, $trailer->getFileControlTotalB());
        self::assertSame($data['numberOfGroups'], $trailer->getNumberOfGroups());
        self::assertSame($data['numberOfRecords'], $trailer->getNumberOfRecords());
    }
}
