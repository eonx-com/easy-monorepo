<?php
declare(strict_types=1);

namespace EonX\EasyAsync\Tests\Unit;

use EonX\EasyAsync\Tests\AbstractTestCase;
use EonX\EasyAsync\Tests\Stub\KernelStub;
use Symfony\Component\HttpKernel\KernelInterface;

abstract class AbstractUnitTestCase extends AbstractTestCase
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
