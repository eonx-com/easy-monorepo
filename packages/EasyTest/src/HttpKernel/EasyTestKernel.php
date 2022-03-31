<?php

declare(strict_types=1);

namespace EonX\EasyTest\HttpKernel;

use Symfony\Component\Config\Loader\LoaderInterface;
use Symfony\Component\HttpKernel\Kernel;

final class EasyTestKernel extends Kernel
{
    public function getCacheDir(): string
    {
        return \sys_get_temp_dir() . '/easy_test';
    }

    public function getLogDir(): string
    {
        return \sys_get_temp_dir() . '/east_test_logs';
    }

    /**
     * @return iterable<\Symfony\Component\HttpKernel\Bundle\BundleInterface>
     */
    public function registerBundles(): iterable
    {
        return [];
    }

    /**
     * @throws \Exception
     */
    public function registerContainerConfiguration(LoaderInterface $loader): void
    {
        $loader->load(__DIR__ . '/../../config/services.yaml');
    }
}
