<?php

declare(strict_types=1);

namespace EonX\EasyRandom\Tests\Bridge\Symfony;

use EonX\EasyRandom\Tests\AbstractTestCase;
use EonX\EasyRandom\Tests\Bridge\Symfony\Stubs\KernelStub;
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
        if ($this->kernel !== null) {
            return $this->kernel;
        }

        $kernel = new KernelStub('test', true, $configs);
        $kernel->boot();

        return $this->kernel = $kernel;
    }
}
