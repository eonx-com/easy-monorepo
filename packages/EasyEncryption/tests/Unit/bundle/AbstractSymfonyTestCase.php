<?php
declare(strict_types=1);

namespace EonX\EasyEncryption\Tests\Unit\Bundle;

use EonX\EasyEncryption\Tests\Stub\Kernel\KernelStub;
use EonX\EasyEncryption\Tests\Unit\AbstractUnitTestCase;
use Symfony\Component\HttpKernel\KernelInterface;

abstract class AbstractSymfonyTestCase extends AbstractUnitTestCase
{
    private ?KernelInterface $kernel = null;

    /**
     * @param string[]|null $configs
     */
    protected function getKernel(?array $configs = null): KernelInterface
    {
        if ($this->kernel !== null && $configs === null) {
            return $this->kernel;
        }

        $this->kernel = new KernelStub($configs);
        $this->kernel->boot();

        return $this->kernel;
    }

    protected function setAppSecret(string $secret): void
    {
        \putenv(\sprintf('APP_SECRET=%s', $secret));
    }
}
