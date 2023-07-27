<?php

declare(strict_types=1);

namespace EonX\EasyPagination\Bridge\Symfony\DependencyInjection;

use EonX\EasyPagination\Bridge\BridgeConstantsInterface;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Extension\Extension;
use Symfony\Component\DependencyInjection\Loader\PhpFileLoader;

final class EasyPaginationExtension extends Extension
{
    private const PAGINATION_PARAMS = [
        'page_attribute' => BridgeConstantsInterface::PARAM_PAGE_ATTRIBUTE,
        'page_default' => BridgeConstantsInterface::PARAM_PAGE_DEFAULT,
        'per_page_attribute' => BridgeConstantsInterface::PARAM_PER_PAGE_ATTRIBUTE,
        'per_page_default' => BridgeConstantsInterface::PARAM_PER_PAGE_DEFAULT,
    ];

    /**
     * @throws \Exception
     */
    public function load(array $configs, ContainerBuilder $container): void
    {
        $config = $this->processConfiguration(new Configuration(), $configs);
        $loader = new PhpFileLoader($container, new FileLocator(__DIR__ . '/../Resources/config'));

        foreach (self::PAGINATION_PARAMS as $name => $param) {
            $container->setParameter($param, $config['pagination'][$name]);
        }

        $loader->load('services.php');

        if ($config['use_default_resolver'] ?? true) {
            $loader->load('default_resolver.php');
        }
    }
}
