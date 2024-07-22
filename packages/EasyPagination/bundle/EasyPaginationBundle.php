<?php
declare(strict_types=1);

namespace EonX\EasyPagination\Bundle;

use EonX\EasyPagination\Bundle\Enum\ConfigParam;
use Symfony\Component\Config\Definition\Configurator\DefinitionConfigurator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use Symfony\Component\HttpKernel\Bundle\AbstractBundle;

final class EasyPaginationBundle extends AbstractBundle
{
    private const PAGINATION_PARAMS = [
        'page_attribute' => ConfigParam::PageAttribute,
        'page_default' => ConfigParam::PageDefault,
        'per_page_attribute' => ConfigParam::PerPageAttribute,
        'per_page_default' => ConfigParam::PerPageDefault,
    ];

    public function __construct()
    {
        $this->path = \realpath(__DIR__);
    }

    public function configure(DefinitionConfigurator $definition): void
    {
        $definition->import('config/definition.php');
    }

    public function loadExtension(array $config, ContainerConfigurator $container, ContainerBuilder $builder): void
    {
        foreach (self::PAGINATION_PARAMS as $name => $param) {
            $container
                ->parameters()
                ->set($param->value, $config['pagination'][$name]);
        }

        $container->import('config/services.php');

        if ($config['use_default_resolver'] ?? true) {
            $container->import('config/default_resolver.php');
        }
    }
}
