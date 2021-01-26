<?php
declare(strict_types=1);

namespace EonX\EasyMonorepo;

use Symfony\Component\Config\Loader\LoaderInterface;
use Symfony\Component\HttpKernel\Kernel;

final class MonorepoKernel extends Kernel
{
    /**
     * @var string
     */
    public const NAMESPACE = 'EonX\\EasyMonorepo\\';

    /**
     * @return \Symfony\Component\HttpKernel\Bundle\BundleInterface[]
     */
    public function registerBundles()
    {
        return [];
    }

    public function registerContainerConfiguration(LoaderInterface $loader): void
    {
        $loader->load(__DIR__ . '/../config/monorepo_services.php');
    }
}
