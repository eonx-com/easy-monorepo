<?php
declare(strict_types=1);

namespace EonX\EasyUtils\Tests\Bridge\Symfony;

use EonX\EasyUtils\Interfaces\MathInterface;
use EonX\EasyUtils\Tests\AbstractMathTestCase;

final class MathTest extends AbstractMathTestCase
{
    use SymfonyTestCaseTrait;

    protected function getMath(): MathInterface
    {
        $container = $this->getKernel()
            ->getContainer();

        return $container->get(MathInterface::class);
    }
}
