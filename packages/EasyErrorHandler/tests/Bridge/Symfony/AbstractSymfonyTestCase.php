<?php
declare(strict_types=1);

namespace EonX\EasyErrorHandler\Tests\Bridge\Symfony;

use EonX\EasyErrorHandler\Tests\Bridge\Symfony\Fixtures\App\Kernel\ApplicationKernel;
use EonX\EasyTest\Common\Trait\ContainerServiceTrait;
use EonX\EasyTest\Common\Trait\PrivatePropertyAccessTrait;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\HttpKernel\KernelInterface;

abstract class AbstractSymfonyTestCase extends KernelTestCase
{
    use ContainerServiceTrait;
    use PrivatePropertyAccessTrait;

    protected function setUp(): void
    {
        self::bootKernel();
    }

    protected static function createKernel(array $options = []): KernelInterface
    {
        return new ApplicationKernel('test', false);
    }
}
