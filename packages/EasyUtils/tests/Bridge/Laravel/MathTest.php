<?php
declare(strict_types=1);

namespace EonX\EasyUtils\Tests\Bridge\Laravel;

use EonX\EasyUtils\Interfaces\MathInterface;
use EonX\EasyUtils\Tests\AbstractMathTestCase;

final class MathTest extends AbstractMathTestCase
{
    use LaravelTestCaseTrait;

    protected function getMath(): MathInterface
    {
        $app = $this->getApplication();

        return $app->make(MathInterface::class);
    }
}
