<?php
declare(strict_types=1);

namespace EonX\EasySwoole\Tests\Stub\Kernel;

use Psr\Log\NullLogger;
use Symfony\Bundle\FrameworkBundle\FrameworkBundle;
use Symfony\Component\Config\Loader\LoaderInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Kernel;

final class FrameworkKernelStub extends Kernel
{
    public function __construct(
        private readonly string $varDir,
    ) {
        parent::__construct('prod', false);
    }

    public function getCacheDir(): string
    {
        return $this->varDir . '/cache';
    }

    public function getLogDir(): string
    {
        return $this->varDir . '/log';
    }

    public function getProjectDir(): string
    {
        return $this->varDir;
    }

    /**
     * @return iterable<\Symfony\Component\HttpKernel\Bundle\BundleInterface>
     */
    public function registerBundles(): iterable
    {
        yield new FrameworkBundle();
    }

    public function registerContainerConfiguration(LoaderInterface $loader): void
    {
        $loader->load(static function (ContainerBuilder $container): void {
            // No routes, so every path ends in a NotFoundHttpException
            $container->loadFromExtension('framework', [
                'router' => [
                    'resource' => __DIR__ . '/../../Fixture/config/routes.php',
                ],
                'secret' => 'secret',
                'validation' => true,
            ]);
            $container->register('logger', NullLogger::class);
        });
    }
}
