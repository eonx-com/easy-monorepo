<?php

declare(strict_types=1);

namespace EonX\EasyCore\Bridge\Symfony\DependencyInjection;

use ApiPlatform\Core\Bridge\Symfony\Bundle\ApiPlatformBundle;
use EonX\EasyAsync\Bridge\Symfony\EasyAsyncBundle;
use EonX\EasyCore\Bridge\Symfony\Interfaces\EventListenerInterface;
use EonX\EasyCore\Bridge\Symfony\Interfaces\TagsInterface;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Extension\Extension;
use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;

final class EasyCoreExtension extends Extension
{
    /**
     * @var \Symfony\Component\DependencyInjection\ContainerBuilder
     */
    private $container;

    /**
     * @var \Symfony\Component\Config\Loader\LoaderInterface
     */
    private $loader;

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

        $this->container = $container;
        $this->loader = $loader;

        $this->registerListenersForAutoconfig($container);
        $this->registerCustomPagination($config);

        $this->loadIfBundleExists('easy_async_listeners.yaml', EasyAsyncBundle::class);
        $this->loadIfBundleExists('iri_converter.yaml', ApiPlatformBundle::class);
    }

    private function loadIfBundleExists(string $resource, string $bundle): void
    {
        if (\in_array($bundle, $this->container->getParameter('kernel.bundles'), true) === false) {
            return;
        }

        $this->loader->load($resource);
    }

    /**
     * @param mixed[] $config
     *
     * @throws \Exception
     */
    private function registerCustomPagination(array $config): void
    {
        if (($config['api_platform']['custom_pagination'] ?? false) === false) {
            return;
        }

        $this->loadIfBundleExists('pagination.yaml', ApiPlatformBundle::class);
    }

    private function registerListenersForAutoconfig(ContainerBuilder $container): void
    {
        $container
            ->registerForAutoconfiguration(EventListenerInterface::class)
            ->addTag(TagsInterface::EVENT_LISTENER_AUTO_CONFIG);
    }
}
