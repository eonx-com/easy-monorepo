<?php
declare(strict_types=1);

namespace EonX\EasyEncryption\Tests\Bridge\Symfony;

use EonX\EasyEncryption\Tests\AbstractTestCase;
use EonX\EasyEncryption\Tests\Bridge\Symfony\Stubs\KernelStub;
use Symfony\Component\HttpKernel\KernelInterface;

abstract class AbstractSymfonyTestCase extends AbstractTestCase
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
