<?php
declare(strict_types=1);

namespace EonX\EasyServerless\Tests\Unit;

use EonX\EasyServerless\Tests\Stub\Kernel\KernelStub;
use Symfony\Component\HttpKernel\Bundle\BundleInterface;
use Symfony\Component\HttpKernel\KernelInterface;

abstract class AbstractSymfonyTestCase extends AbstractUnitTestCase
{
    private ?KernelInterface $kernel = null;

    /**
     * @param array<int, class-string<BundleInterface>>|null $extraBundles
     * @param string[]|null $configs
     */
    protected function getKernel(?array $configs = null, ?array $extraBundles = null): KernelInterface
    {
        if ($this->kernel !== null && $configs === null && $extraBundles === null) {
            return $this->kernel;
        }

        $this->kernel = new KernelStub('test', true, $configs, $extraBundles);
        $this->kernel->boot();

        return $this->kernel;
    }
}
