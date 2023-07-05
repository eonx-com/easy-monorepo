<?php

declare(strict_types=1);

namespace EonX\EasyApiPlatform\Tests\Bridge\Symfony;

use EonX\EasyApiPlatform\Tests\AbstractTestCase;
use EonX\EasyApiPlatform\Tests\Bridge\Symfony\Stubs\KernelStub;
use Symfony\Component\HttpKernel\KernelInterface;

abstract class AbstractSymfonyTestCase extends AbstractTestCase
{
    private ?KernelInterface $kernel = null;

    protected function getKernel(): KernelInterface
    {
        if ($this->kernel !== null) {
            return $this->kernel;
        }

        $this->kernel = new KernelStub('test', true);
        $this->kernel->boot();

        return $this->kernel;
    }
}
