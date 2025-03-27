<?php
declare(strict_types=1);

namespace EonX\EasyServerless\Tests\Unit\Laravel\HttpKernel;

use EonX\EasyServerless\Tests\Unit\Laravel\AbstractLumenTestCase;
use LumenHttpKernel;

final class LumenHttpKernelTest extends AbstractLumenTestCase
{
    public function testBootstrapFails(): void
    {
        $this->expectException(\RuntimeException::class);

        $kernel = new LumenHttpKernel($this->getApplication());

        $kernel->bootstrap();
    }

    public function testGetApplicationFails(): void
    {
        $this->expectException(\RuntimeException::class);

        $kernel = new LumenHttpKernel($this->getApplication());

        $kernel->getApplication();
    }
}
