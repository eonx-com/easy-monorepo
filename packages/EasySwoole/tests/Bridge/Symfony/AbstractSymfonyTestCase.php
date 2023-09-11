<?php
declare(strict_types=1);

namespace EonX\EasySwoole\Tests\Bridge\Symfony;

use EonX\EasySwoole\Tests\AbstractTestCase;
use EonX\EasySwoole\Tests\Bridge\Symfony\Stubs\KernelStub;
use Symfony\Component\HttpKernel\KernelInterface;

abstract class AbstractSymfonyTestCase extends AbstractTestCase
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

        $this->kernel = new KernelStub($configs);
        $this->kernel->boot();

        return $this->kernel;
    }
}
