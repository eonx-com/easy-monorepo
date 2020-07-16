<?php

declare(strict_types=1);

namespace EonX\EasyEventDispatcher\Tests\Bridge\Symfony;

use EonX\EasyEventDispatcher\Tests\AbstractTestCase;
use EonX\EasyEventDispatcher\Tests\Bridge\Symfony\Stubs\KernelStub;
use Symfony\Component\HttpKernel\KernelInterface;

abstract class AbstractSymfonyTestCase extends AbstractTestCase
{
    /**
     * @var \Symfony\Component\HttpKernel\KernelInterface
     */
    private $kernel;

    protected function getKernel(): KernelInterface
    {
        if ($this->kernel !== null) {
            return $this->kernel;
        }

        $kernel = new KernelStub('test', true);
        $kernel->boot();

        return $this->kernel = $kernel;
    }
}
