<?php

declare(strict_types=1);

namespace EonX\EasyPagination\Bridge\Symfony\DependencyInjection;

use EonX\EasyPagination\Bridge\BridgeConstantsInterface;
use EonX\EasyPagination\Interfaces\StartSizeDataResolverInterface;
use EonX\EasyPagination\Resolvers\StartSizeAsArrayInQueryResolver;
use EonX\EasyPagination\Resolvers\StartSizeInQueryResolver;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Extension\Extension;
use Symfony\Component\DependencyInjection\Loader\PhpFileLoader;

final class EasyPaginationExtension extends Extension
{
    /**
     * @var string[]
     */
    private const PAGINATION_PARAMS = [
        'page_attribute' => BridgeConstantsInterface::PARAM_PAGE_ATTRIBUTE,
        'page_default' => BridgeConstantsInterface::PARAM_PAGE_DEFAULT,
        'per_page_attribute' => BridgeConstantsInterface::PARAM_PER_PAGE_ATTRIBUTE,
        'per_page_default' => BridgeConstantsInterface::PARAM_PER_PAGE_DEFAULT,
    ];

    /**
     * @var string[]
     */
    private const RESOLVERS = [
        'array_in_query' => StartSizeAsArrayInQueryResolver::class,
        'in_query' => StartSizeInQueryResolver::class,
    ];

    /**
     * @param mixed[] $configs
     *
     * @throws \Exception
     */
    public function load(array $configs, ContainerBuilder $container): void
    {
        $config = $this->processConfiguration(new Configuration(), $configs);
        $loader = new PhpFileLoader($container, new FileLocator(__DIR__ . '/../Resources/config'));

        $this->loadDeprecated($config, $container, $loader);

        foreach (self::PAGINATION_PARAMS as $name => $param) {
            $container->setParameter($param, $config['pagination'][$name]);
        }

        $loader->load('services.php');

        if ($config['use_default_resolver'] ?? true) {
            $loader->load('default_resolver.php');
        }
    }

    /**
     * @param mixed[] $config
     *
     * @throws \Exception
     */
    private function loadDeprecated(array $config, ContainerBuilder $container, PhpFileLoader $loader): void
    {
        $loader->load('services_deprecated.php');

        $container->setParameter('easy_pagination.start_size_config', $config['start_size']);
        $container->setParameter('easy_pagination.array_in_query_attr', $config['array_in_query_attr']);

        $container->setAlias(StartSizeDataResolverInterface::class, self::RESOLVERS[$config['resolver']]);
    }
}
