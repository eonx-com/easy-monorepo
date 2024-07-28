<?php
declare(strict_types=1);

namespace EonX\EasyEncryption\HashCalculator;

interface HashCalculatorInterface
{
    public function calculate(string $value): string;
}
