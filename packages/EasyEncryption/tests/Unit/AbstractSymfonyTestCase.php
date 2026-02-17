<?php
declare(strict_types=1);

namespace EonX\EasyEncryption\Tests\Unit;

use EonX\EasyEncryption\Tests\Stub\Kernel\KernelStub;
use Symfony\Component\HttpKernel\KernelInterface;

abstract class AbstractSymfonyTestCase extends AbstractUnitTestCase
{
    /**
     * @param string[]|null $configs
     */
    protected function getKernel(?array $configs = null): KernelInterface
    {
        self::ensureKernelShutdown();

        self::$kernel = new KernelStub($configs);
        self::$kernel->boot();

        return self::$kernel;
    }

    protected function setAppSecret(string $secret): void
    {
        \putenv(\sprintf('APP_SECRET=%s', $secret));
    }
}
