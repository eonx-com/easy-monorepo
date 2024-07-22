<?php
declare(strict_types=1);

namespace EonX\EasyMonorepo\Kernel;

use Symfony\Component\Config\Loader\LoaderInterface;
use Symfony\Component\HttpKernel\Kernel;

final class MonorepoKernel extends Kernel
{
    public const NAMESPACE = 'EonX\\EasyMonorepo\\';

    /**
     * @return \Symfony\Component\HttpKernel\Bundle\BundleInterface[]
     */
    public function registerBundles(): array
    {
        return [];
    }

    public function registerContainerConfiguration(LoaderInterface $loader): void
    {
        $loader->load(__DIR__ . '/../../config/monorepo_services.php');
    }
}
