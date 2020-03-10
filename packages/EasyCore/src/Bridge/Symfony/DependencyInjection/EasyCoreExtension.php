<?php
declare(strict_types=1);

namespace EonX\EasyCore\Bridge\Symfony\DependencyInjection;

use EonX\EasyAsync\Bridge\Symfony\EasyAsyncBundle;
use EonX\EasyCore\Bridge\Symfony\Security\PermissionExpressionFunctionProvider;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\Config\Loader\LoaderInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Extension\Extension;
use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;

final class EasyCoreExtension extends Extension
{
    /**
     * @param mixed[] $configs
     *
     * @throws \Exception
     */
    public function load(array $configs, ContainerBuilder $container): void
    {
        $config = $this->processConfiguration(new Configuration(), $configs);

        $loader = new YamlFileLoader($container, new FileLocator(__DIR__ . '/../Resources/config'));
        $loader->load('services.yaml');

        $this->registerCustomPagination($config, $loader);
        $this->registerEasyAsyncListeners($container, $loader);
    }

    /**
     * @param mixed[] $config
     *
     * @throws \Exception
     */
    private function registerCustomPagination(array $config, LoaderInterface $loader): void
    {
        if (($config['api_platform']['custom_pagination'] ?? false) === false) {
            return;
        }

        if (\class_exists('ApiPlatform\Core\Bridge\Doctrine\Orm\Paginator') === true) {
            $loader->load('pagination.yaml');
        }
    }

    private function registerEasyAsyncListeners(ContainerBuilder $container, LoaderInterface $loader): void
    {
        if (\in_array(EasyAsyncBundle::class, $container->getParameter('kernel.bundles'), true) === false) {
            return;
        }

        $loader->load('easy_async_listeners.yaml');
    }
}
