<?php
declare(strict_types=1);

namespace EonX\EasyRequestId\Tests\Unit\Bundle;

use EonX\EasyRequestId\Tests\Stub\HttpKernel\KernelStub;
use EonX\EasyRequestId\Tests\Unit\AbstractUnitTestCase;
use Symfony\Component\HttpKernel\KernelInterface;

abstract class AbstractSymfonyTestCase extends AbstractUnitTestCase
{
    private ?KernelInterface $kernel = null;

    protected function getKernel(?array $configs = null): KernelInterface
    {
        if ($this->kernel !== null) {
            return $this->kernel;
        }

        $this->kernel = new KernelStub($configs);
        $this->kernel->boot();

        return $this->kernel;
    }
}
