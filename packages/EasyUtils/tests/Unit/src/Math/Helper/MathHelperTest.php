<?php
declare(strict_types=1);

namespace EonX\EasyUtils\Tests\Unit\Math\Helper;

use EonX\EasyUtils\Math\Helper\MathHelper;
use EonX\EasyUtils\Math\Helper\MathHelperInterface;
use EonX\EasyUtils\Tests\Unit\AbstractMathHelperTestCase;

final class MathHelperTest extends AbstractMathHelperTestCase
{
    protected function getMath(): MathHelperInterface
    {
        return new MathHelper();
    }
}
