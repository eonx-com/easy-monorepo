<?php
declare(strict_types=1);

namespace EonX\EasyActivity\Tests\Bridge\Symfony;

use EonX\EasyActivity\Tests\AbstractTestCase;
use EonX\EasyActivity\Tests\Bridge\Symfony\Stubs\KernelStub;
use Symfony\Component\HttpKernel\KernelInterface;

abstract class AbstractSymfonyTestCase extends AbstractTestCase
{
    /**
     * @param string[]|null $configs
     */
    protected function getKernel(?array $configs = null): KernelInterface
    {
        $kernel = new KernelStub($configs);
        $kernel->boot();

        return $kernel;
    }
}
