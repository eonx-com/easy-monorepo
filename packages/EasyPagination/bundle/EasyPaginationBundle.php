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
        $container
            ->parameters()
            ->set(ConfigParam::PageAttribute->value, $config['pagination']['page_attribute'])
            ->set(ConfigParam::PageDefault->value, $config['pagination']['page_default'])
            ->set(ConfigParam::PerPageAttribute->value, $config['pagination']['per_page_attribute'])
            ->set(ConfigParam::PerPageDefault->value, $config['pagination']['per_page_default']);

        $container->import('config/services.php');

        if ($config['use_default_resolver']) {
            $container->import('config/default_resolver.php');
        }
    }
}
