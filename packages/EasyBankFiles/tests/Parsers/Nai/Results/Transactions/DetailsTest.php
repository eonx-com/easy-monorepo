<?php
declare(strict_types=1);

namespace EonX\EasyBankFiles\Tests\Parsers\Nai\Results\Transactions;

use EonX\EasyBankFiles\Parsers\Nai\Results\Transactions\Details;
use EonX\EasyBankFiles\Tests\Parsers\TestCase;

/**
 * @covers \EonX\EasyBankFiles\Parsers\Nai\Results\Transactions\Details
 */
final class DetailsTest extends TestCase
{
    /**
     * Result should return data as expected.
     */
    public function testGetDataAsExpected(): void
    {
        $data = [
            'description' => 'description',
            'particulars' => 'particulars',
            'type' => 'type',
        ];

        $details = new Details($data);

        self::assertSame($data['description'], $details->getDescription());
        self::assertSame($data['particulars'], $details->getParticulars());
        self::assertSame($data['type'], $details->getType());
    }
}
