<?php
declare(strict_types=1);

namespace EonX\EasyDoctrine\Tests\Stub\HttpKernel;

use EonX\EasyDoctrine\Bundle\EasyDoctrineBundle;
use Symfony\Component\Config\Loader\LoaderInterface;
use Symfony\Component\HttpKernel\Kernel;

final class KernelStub extends Kernel
{
    /**
     * @return iterable<\Symfony\Component\HttpKernel\Bundle\BundleInterface>
     */
    public function registerBundles(): iterable
    {
        yield new EasyDoctrineBundle();
    }

    public function registerContainerConfiguration(LoaderInterface $loader): void
    {
        // No body needed
    }
}
