<?php

declare(strict_types=1);

namespace EonX\EasyCore\Tests\Bridge\Symfony\Stubs;

use EonX\EasyCore\Bridge\Symfony\EasyCoreSymfonyBundle;
use Symfony\Component\Config\Loader\LoaderInterface;
use Symfony\Component\HttpKernel\Kernel;

final class KernelStub extends Kernel
{
    /**
     * @return iterable<\Symfony\Component\HttpKernel\Bundle\BundleInterface>
     */
    public function registerBundles(): iterable
    {
        yield new EasyCoreSymfonyBundle();
    }

    public function registerContainerConfiguration(LoaderInterface $loader): void
    {
        // No body needed.
    }
}
