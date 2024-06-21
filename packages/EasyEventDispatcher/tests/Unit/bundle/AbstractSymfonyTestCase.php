<?php
declare(strict_types=1);

namespace EonX\EasyEventDispatcher\Tests\Unit\Bundle;

use EonX\EasyEventDispatcher\Tests\Stub\HttpKernel\KernelStub;
use EonX\EasyEventDispatcher\Tests\Unit\AbstractUnitTestCase;
use Symfony\Component\HttpKernel\KernelInterface;

abstract class AbstractSymfonyTestCase extends AbstractUnitTestCase
{
    private ?KernelInterface $kernel = null;

    protected function getKernel(): KernelInterface
    {
        if ($this->kernel !== null) {
            return $this->kernel;
        }

        $this->kernel = new KernelStub('test', true);
        $this->kernel->boot();

        return $this->kernel;
    }
}
