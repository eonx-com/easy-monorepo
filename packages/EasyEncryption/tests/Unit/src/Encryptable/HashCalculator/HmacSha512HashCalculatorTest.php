<?php
declare(strict_types=1);

namespace EonX\EasyEncryption\Tests\Unit\Encryptable\HashCalculator;

use EonX\EasyEncryption\Encryptable\HashCalculator\HmacSha512HashCalculator;
use EonX\EasyEncryption\Tests\Unit\AbstractUnitTestCase;

final class HmacSha512HashCalculatorTest extends AbstractUnitTestCase
{
    public function testCalculateSucceeds(): void
    {
        $calculator = new HmacSha512HashCalculator('secret');

        $hash = $calculator->calculate('Some Value');

        self::assertSame(\hash_hmac('sha512', 'Some Value', 'secret'), $hash);
    }

    public function testCalculateSucceedsWithoutNormalizingCase(): void
    {
        $calculator = new HmacSha512HashCalculator('secret');

        $upperCaseHash = $calculator->calculate('Value');
        $lowerCaseHash = $calculator->calculate('value');

        self::assertNotSame($upperCaseHash, $lowerCaseHash);
    }
}
