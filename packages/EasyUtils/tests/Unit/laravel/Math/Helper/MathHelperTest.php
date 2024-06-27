<?php
declare(strict_types=1);

namespace EonX\EasyUtils\Tests\Unit\Laravel\Math\Helper;

use EonX\EasyUtils\Math\Helper\MathHelperInterface;
use EonX\EasyUtils\Tests\Unit\AbstractMathHelperTestCase;
use EonX\EasyUtils\Tests\Unit\Laravel\LaravelTestCaseTrait;

final class MathHelperTest extends AbstractMathHelperTestCase
{
    use LaravelTestCaseTrait;

    protected function getMath(): MathHelperInterface
    {
        $app = $this->getApplication();

        return $app->make(MathHelperInterface::class);
    }
}
