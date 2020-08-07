<?php

declare(strict_types=1);

namespace EonX\EasyCore\Bridge\Symfony\DependencyInjection;

use ApiPlatform\Core\Bridge\Symfony\Bundle\ApiPlatformBundle;
use EonX\EasyAsync\Bridge\Symfony\EasyAsyncBundle;
use EonX\EasyCore\Bridge\Symfony\ApiPlatform\Interfaces\SimpleDataPersisterInterface;
use EonX\EasyCore\Bridge\Symfony\Interfaces\EventListenerInterface;
use EonX\EasyCore\Bridge\Symfony\Interfaces\TagsInterface;
use Symfony\Bundle\MakerBundle\MakerBundle;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Extension\Extension;
use Symfony\Component\DependencyInjection\Loader\PHPFileLoader;

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

        $loader = new PHPFileLoader($container, new FileLocator(__DIR__ . '/../Resources/config'));
        $loader->load('services.php');

        $this->container = $container;
        $this->loader = $loader;

        $this->autoconfigTag(EventListenerInterface::class, TagsInterface::EVENT_LISTENER_AUTO_CONFIG);
        $this->autoconfigTag(SimpleDataPersisterInterface::class, TagsInterface::SIMPLE_DATA_PERSISTER_AUTO_CONFIG);

        $this->loadIfBundlesExists('easy_async_listeners.php', EasyAsyncBundle::class);
        $this->loadIfBundlesExists('api_platform/iri_converter.php', ApiPlatformBundle::class);
        $this->loadIfBundlesExists('api_platform/maker_commands.php', [ApiPlatformBundle::class, MakerBundle::class]);

        if ($config['api_platform']['custom_pagination_enabled'] ?? false) {
            $this->loadIfBundlesExists('api_platform/pagination.php', ApiPlatformBundle::class);
        }

        if ($config['api_platform']['no_properties_api_resource_enabled'] ?? false) {
            $this->loadIfBundlesExists('api_platform/no_properties_api_resource.php', ApiPlatformBundle::class);
        }

        if ($config['api_platform']['simple_data_persister_enabled'] ?? false) {
            $this->loadIfBundlesExists('api_platform/simple_data_persister.php', ApiPlatformBundle::class);

            // This debug file is only for when simple data persister is enabled
            if ($container->hasParameter('kernel.debug') && $container->getParameter('kernel.debug')) {
                $loader->load('api_platform/debug.php');
            }
        }
    }

    /**
     * @param null|mixed[] $attributes
     */
    private function autoconfigTag(string $interface, string $tag, ?array $attributes = null): void
    {
        $this->container->registerForAutoconfiguration($interface)->addTag($tag, $attributes ?? []);
    }

    /**
     * @param string|string[] $bundles
     *
     * @throws \Exception
     */
    private function loadIfBundlesExists(string $resource, $bundles): void
    {
        foreach ((array)$bundles as $bundle) {
            if (\in_array($bundle, $this->container->getParameter('kernel.bundles'), true) === false) {
                return;
            }
        }

        $this->loader->load($resource);
    }
}
