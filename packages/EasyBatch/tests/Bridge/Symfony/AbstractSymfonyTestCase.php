<?php

declare(strict_types=1);

namespace EonX\EasyBatch\Tests\Bridge\Symfony;

use EonX\EasyBatch\Tests\AbstractTestCase;
use EonX\EasyBatch\Tests\Bridge\Symfony\Stubs\KernelStub;
use Symfony\Component\HttpKernel\KernelInterface;

abstract class AbstractSymfonyTestCase extends AbstractTestCase
{
    protected function getKernel(): KernelInterface
    {
        $kernel = new KernelStub('test', true);
        $kernel->boot();

        return $kernel;
    }
}
