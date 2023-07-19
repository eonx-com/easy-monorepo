<?php

declare(strict_types=1);

namespace EonX\EasyApiPlatform\Bridge\Symfony\DependencyInjection;

use EonX\EasyApiPlatform\Bridge\BridgeConstantsInterface;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Extension\Extension;
use Symfony\Component\DependencyInjection\Loader\PhpFileLoader;

final class EasyApiPlatformExtension extends Extension
{
    /**
     * @param mixed[] $configs
     *
     * @throws \Exception
     */
    public function load(array $configs, ContainerBuilder $container): void
    {
        $config = $this->processConfiguration(new Configuration(), $configs);
        $loader = new PhpFileLoader($container, new FileLocator(__DIR__ . '/../Resources/config'));

        $container->setParameter(
            BridgeConstantsInterface::PARAM_ADVANCED_SEARCH_FILTER_IRI_FIELDS,
            $config['advanced_search_filter']['iri_fields']
        );

        $loader->load('services.php');
        $loader->load('filters.php');
    }
}
