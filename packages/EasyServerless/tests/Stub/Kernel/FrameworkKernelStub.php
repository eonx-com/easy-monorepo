<?php
declare(strict_types=1);

namespace EonX\EasyServerless\Tests\Stub\Kernel;

use EonX\EasyServerless\Bundle\EasyServerlessBundle;
use Symfony\Bundle\FrameworkBundle\FrameworkBundle;
use Symfony\Component\Config\Loader\LoaderInterface;
use Symfony\Component\HttpKernel\Kernel;

/**
 * Unlike KernelStub, this kernel compiles the container with the real FrameworkBundle compiler passes.
 */
final class FrameworkKernelStub extends Kernel
{
    /**
     * @param string[] $configs
     */
    public function __construct(
        string $environment,
        bool $debug,
        private readonly array $configs,
    ) {
        parent::__construct($environment, $debug);
    }

    /**
     * @return iterable<\Symfony\Component\HttpKernel\Bundle\BundleInterface>
     */
    public function registerBundles(): iterable
    {
        yield new FrameworkBundle();
        yield new EasyServerlessBundle();
    }

    public function registerContainerConfiguration(LoaderInterface $loader): void
    {
        foreach ($this->configs as $config) {
            $loader->load($config);
        }
    }
}
