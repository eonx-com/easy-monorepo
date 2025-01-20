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
        $randomNumber = \str_pad(
            (string)\random_int(1, 999999999),
            9,
            '0',
            \STR_PAD_LEFT
        );

        $abn = '10' . $randomNumber;

        $sum = 0;
        foreach (self::WEIGHTS as $position => $weight) {
            $digit = (int)$abn[$position] - ($position !== 0 ? 0 : 1);
            $sum += $weight * $digit;
        }

        return ((self::MASK - ($sum % 89)) + 10) . $randomNumber;
    }
}
