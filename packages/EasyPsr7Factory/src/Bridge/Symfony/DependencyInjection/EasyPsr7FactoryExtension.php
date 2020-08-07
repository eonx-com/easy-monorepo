<?php

declare(strict_types=1);

namespace EonX\EasyPsr7Factory\Bridge\Symfony\DependencyInjection;

use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Extension\Extension;
use Symfony\Component\DependencyInjection\Loader\PHPFileLoader;

final class EasyPsr7FactoryExtension extends Extension
{
    /**
     * @param mixed[] $configs
     *
     * @throws \Exception
     */
    public function load(array $configs, ContainerBuilder $container): void
    {
        (new PHPFileLoader($container, new FileLocator(__DIR__ . '/../Resources/config')))->load('services.php');
    }
}
