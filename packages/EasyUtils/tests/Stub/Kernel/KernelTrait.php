<?php
declare(strict_types=1);

namespace EonX\EasyUtils\Tests\Stub\Kernel;

use Symfony\Component\HttpKernel\KernelInterface;

trait KernelTrait
{
    private ?KernelInterface $kernel = null;

    protected function getKernel(?array $configs = null): KernelInterface
    {
        if ($this->kernel !== null) {
            return $this->kernel;
        }

        $this->kernel = new KernelStub('test', true, $configs);
        $this->kernel->boot();

        return $this->kernel;
    }
}
