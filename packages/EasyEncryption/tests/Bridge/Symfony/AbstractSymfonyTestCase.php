<?php

declare(strict_types=1);

namespace EonX\EasyEncryption\Tests\Bridge\Symfony;

use EonX\EasyEncryption\Tests\AbstractTestCase;
use EonX\EasyEncryption\Tests\Bridge\Symfony\Stubs\KernelStub;
use Symfony\Component\HttpKernel\KernelInterface;

abstract class AbstractSymfonyTestCase extends AbstractTestCase
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
        if ($this->kernel !== null && $configs === null) {
            return $this->kernel;
        }

        $kernel = new KernelStub($configs);
        $kernel->boot();

        return $this->kernel = $kernel;
    }

    protected function setAppSecret(string $secret): void
    {
        \putenv(\sprintf('APP_SECRET=%s', $secret));
    }
}
