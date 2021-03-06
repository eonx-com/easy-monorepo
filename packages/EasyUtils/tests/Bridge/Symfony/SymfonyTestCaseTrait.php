<?php

declare(strict_types=1);

namespace EonX\EasyUtils\Tests\Bridge\Symfony;

use EonX\EasyUtils\Tests\Bridge\Symfony\Stubs\KernelStub;
use Symfony\Component\HttpKernel\KernelInterface;

trait SymfonyTestCaseTrait
{
    /**
     * @var \Symfony\Component\HttpKernel\KernelInterface
     */
    private $kernel;

    /**
     * @param null|string[] $configs
     */
    protected function getKernel(?array $configs = null): KernelInterface
    {
        if ($this->kernel !== null) {
            return $this->kernel;
        }

        $kernel = new KernelStub('test', true, $configs);
        $kernel->boot();

        return $this->kernel = $kernel;
    }
}
