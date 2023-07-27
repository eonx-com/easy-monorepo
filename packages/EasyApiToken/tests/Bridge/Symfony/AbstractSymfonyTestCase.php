<?php
declare(strict_types=1);

namespace EonX\EasyApiToken\Tests\Bridge\Symfony;

use EonX\EasyApiToken\Tests\AbstractTestCase;
use EonX\EasyApiToken\Tests\Bridge\Symfony\Stubs\KernelStub;
use Symfony\Component\HttpKernel\KernelInterface;

abstract class AbstractSymfonyTestCase extends AbstractTestCase
{
    protected function getKernel(?array $configs = null): KernelInterface
    {
        $kernel = new KernelStub($configs);
        $kernel->boot();

        return $kernel;
    }
}
