<?php
declare(strict_types=1);

namespace EonX\EasyApiToken\Tests\Unit;

use EonX\EasyApiToken\Tests\Stub\HttpKernel\KernelStub;
use Symfony\Component\HttpKernel\KernelInterface;

abstract class AbstractSymfonyTestCase extends AbstractUnitTestCase
{
    protected function getKernel(?array $configs = null): KernelInterface
    {
        $kernel = new KernelStub($configs);
        $kernel->boot();

        return $kernel;
    }
}
