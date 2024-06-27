<?php
declare(strict_types=1);

namespace EonX\EasySecurity\Tests\Unit;

use EonX\EasySecurity\Tests\Stub\Kernel\KernelStub;
use Symfony\Component\HttpKernel\KernelInterface;

abstract class AbstractSymfonyTestCase extends AbstractUnitTestCase
{
    private ?KernelInterface $kernel = null;

    /**
     * @param string[]|null $configs
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
