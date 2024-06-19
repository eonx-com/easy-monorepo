<?php
declare(strict_types=1);

namespace EonX\EasyBankFiles\Tests\Unit\Parsing\Nai\ValueObject;

use EonX\EasyBankFiles\Parsing\Nai\ValueObject\TransactionDetails;
use EonX\EasyBankFiles\Tests\Unit\AbstractUnitTestCase;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(TransactionDetails::class)]
final class TransactionDetailsTest extends AbstractUnitTestCase
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

        $details = new TransactionDetails($data);

        self::assertSame($data['description'], $details->getDescription());
        self::assertSame($data['particulars'], $details->getParticulars());
        self::assertSame($data['type'], $details->getType());
    }
}
