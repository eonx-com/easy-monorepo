<?php

declare(strict_types=1);

namespace EonX\EasyUtils\Tests\Bridge\Symfony;

use EonX\EasyUtils\Tests\Bridge\Symfony\Stubs\KernelStub;
use Symfony\Component\HttpKernel\KernelInterface;

trait SymfonyTestCaseTrait
{
    private ?KernelInterface $kernel = null;

    /**
     * @param mixed[]|null $configs
     */
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
