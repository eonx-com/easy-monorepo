<?php
declare(strict_types=1);

namespace EonX\EasyEventDispatcher\Bridge\Symfony\DependencyInjection;

use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Extension\Extension;
use Symfony\Component\DependencyInjection\Loader\PhpFileLoader;

final class EasyEventDispatcherExtension extends Extension
{
    /**
     * @throws \Exception
     */
    public function load(array $configs, ContainerBuilder $container): void
    {
        (new PhpFileLoader($container, new FileLocator(__DIR__ . '/../Resources/config')))->load('services.php');
    }
}
