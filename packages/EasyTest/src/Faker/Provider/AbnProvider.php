<?php
declare(strict_types=1);

namespace EonX\EasyTest\Faker\Provider;

use Faker\Provider\Base;

final class AbnProvider extends Base
{
    private const MASK = 89;

    private const WEIGHTS = [10, 1, 3, 5, 7, 9, 11, 13, 15, 17, 19];

    /**
     * Generates Australian Business Number.
     */
    public function abn(): string
    {
        $digits = [];

        do {
            for ($position = 0; $position < 11; $position++) {
                if ($position === 0) {
                    $digits[$position] = \random_int(1, 9);
                }
                if ($position > 0) {
                    $digits[$position] = \random_int(0, 9);
                }
            }
        } while ($this->validateAbn($digits) === false);

        return \implode('', $digits);
    }

    /**
     * @param int[] $digits
     */
    private function validateAbn(array $digits): bool
    {
        $clone = [];

        foreach ($digits as $index => $digit) {
            $clone[$index] = $digit;
        }

        --$clone[0];

        $checksum = 0;
        foreach ($clone as $index => $digit) {
            $checksum += self::WEIGHTS[$index] * $digit;
        }

        return $checksum % self::MASK === 0;
    }
}
