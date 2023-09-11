<?php
declare(strict_types=1);

namespace EonX\EasyDoctrine\Tests\Bridge\Symfony\Stubs;

use EonX\EasyDoctrine\Bridge\Symfony\EasyDoctrineSymfonyBundle;
use Symfony\Component\Config\Loader\LoaderInterface;
use Symfony\Component\HttpKernel\Kernel;

final class KernelStub extends Kernel
{
    /**
     * @return iterable<\Symfony\Component\HttpKernel\Bundle\BundleInterface>
     */
    public function registerBundles(): iterable
    {
        yield new EasyDoctrineSymfonyBundle();
    }

    public function registerContainerConfiguration(LoaderInterface $loader): void
    {
        // No body needed
    }
}
