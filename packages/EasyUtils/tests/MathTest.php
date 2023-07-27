<?php
declare(strict_types=1);

namespace EonX\EasyUtils\Tests;

use EonX\EasyUtils\Interfaces\MathInterface;
use EonX\EasyUtils\Math\Math;

final class MathTest extends AbstractMathTestCase
{
    protected function getMath(): MathInterface
    {
        return new Math();
    }
}
