<?php
declare(strict_types=1);

namespace EonX\EasyUtils\Tests\Unit\Bundle\Math\Helper;

use EonX\EasyUtils\Math\Helper\MathHelperInterface;
use EonX\EasyUtils\Tests\Stub\Trait\KernelTrait;
use EonX\EasyUtils\Tests\Unit\AbstractMathHelperTestCase;

final class MathHelperTest extends AbstractMathHelperTestCase
{
    use KernelTrait;

    protected function getMath(): MathHelperInterface
    {
        $container = $this->getKernel()
            ->getContainer();

        return $container->get(MathHelperInterface::class);
    }
}
