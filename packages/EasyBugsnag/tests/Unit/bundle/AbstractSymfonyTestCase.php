<?php
declare(strict_types=1);

namespace EonX\EasyBugsnag\Tests\Unit\Bundle;

use EonX\EasyBugsnag\Tests\Stub\Kernel\KernelStub;
use EonX\EasyBugsnag\Tests\Unit\AbstractUnitTestCase;
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

        $this->kernel = new KernelStub($configs);
        $this->kernel->boot();

        return $this->kernel;
    }
}