<?php

declare(strict_types=1);

namespace EonX\EasyCore\Bridge\Symfony\DependencyInjection;

use EonX\EasyCore\Bridge\BridgeConstantsInterface;
use EonX\EasyCore\Bridge\Symfony\Interfaces\EventListenerInterface;
use EonX\EasyCore\Bridge\Symfony\Interfaces\TagsInterface;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Extension\Extension;
use Symfony\Component\DependencyInjection\Loader\PhpFileLoader;

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

        $loader = new PhpFileLoader($container, new FileLocator(__DIR__ . '/../Resources/config'));
        $loader->load('services.php');

        $this->container = $container;
        $this->loader = $loader;

        $this->autoconfigTag(EventListenerInterface::class, TagsInterface::EVENT_LISTENER_AUTO_CONFIG);

        // Search
        if ($config['search']['enabled'] ?? false) {
            $container->setParameter(
                BridgeConstantsInterface::PARAM_ELASTICSEARCH_HOST,
                $config['search']['elasticsearch_host']
            );

            $loader->load('search.php');
        }

        // Profiler storage
        if ($config['profiler_storage']['enabled'] ?? false) {
            $loader->load('profiler_storage.php');
        }

        // Trim strings
        if ($config['trim_strings']['enabled'] ?? false) {
            $container->setParameter(
                BridgeConstantsInterface::PARAM_TRIM_STRINGS_EXCEPT,
                $config['trim_strings']['except']
            );

            $loader->load('trim_strings.php');
        }
    }

    /**
     * @param null|mixed[] $attributes
     */
    private function autoconfigTag(string $interface, string $tag, ?array $attributes = null): void
    {
        $this->container->registerForAutoconfiguration($interface)
            ->addTag($tag, $attributes ?? []);
    }
}
