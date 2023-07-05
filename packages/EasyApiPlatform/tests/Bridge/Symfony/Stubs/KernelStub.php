<?php

declare(strict_types=1);

namespace EonX\EasyApiPlatform\Tests\Bridge\Symfony\Stubs;

use EonX\EasyApiPlatform\Bridge\Symfony\EasyApiPlatformSymfonyBundle;
use Symfony\Component\Config\Loader\LoaderInterface;
use Symfony\Component\HttpKernel\Kernel;

final class KernelStub extends Kernel
{
    /**
     * @return iterable<\Symfony\Component\HttpKernel\Bundle\BundleInterface>
     */
    public function registerBundles(): iterable
    {
        yield new EasyApiPlatformSymfonyBundle();
    }

    public function registerContainerConfiguration(LoaderInterface $loader): void
    {
    }
}
