<?php

declare(strict_types=1);

namespace EonX\EasyPagination\Bridge\Symfony\DependencyInjection;

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
     * @var mixed[]
     */
    private static $resolvers = [
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
        (new PhpFileLoader($container, new FileLocator(__DIR__ . '/../Resources/config')))->load('services.php');

        $config = $this->processConfiguration(new Configuration(), $configs);

        $container->setParameter('easy_pagination.start_size_config', $config['start_size']);
        $container->setParameter('easy_pagination.array_in_query_attr', $config['array_in_query_attr']);

        $container->setAlias(StartSizeDataResolverInterface::class, static::$resolvers[$config['resolver']]);
    }
}
