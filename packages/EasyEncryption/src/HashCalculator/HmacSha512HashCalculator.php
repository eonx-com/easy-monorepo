<?php
declare(strict_types=1);

namespace EonX\EasyEncryption\HashCalculator;

final class HmacSha512HashCalculator implements HashCalculatorInterface
{
    public function __construct(
        private string $secret,
    ) {
    }

    public function calculate(string $value): string
    {
        return \hash_hmac('sha512', \mb_strtolower($value), $this->secret);
    }
}
